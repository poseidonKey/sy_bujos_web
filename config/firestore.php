<?php
/**
 * Firebase Firestore REST API 클라이언트
 * gRPC 확장 없이 HTTP REST 로 작동
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FirestoreClient {
    private Client $http;
    private string $baseUrl;
    private string $token;

    public function __construct() {
        $this->token = $this->getAccessToken();
        $projectId = $_ENV['FIRESTORE_PROJECT_ID'] ?? '';
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents";

        $this->http = new Client([
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    private function getAccessToken(): string {
        $keyPath = $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? '';
        if (!file_exists($keyPath)) {
            throw new RuntimeException("Service account key not found: {$keyPath}");
        }

        $keyData = json_decode(file_get_contents($keyPath), true);
        if (!$keyData) {
            throw new RuntimeException("Invalid service account key format");
        }

        // JWT 생성
        $now = time();
        $jwtPayload = [
            'iss' => $keyData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/datastore',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $jwt = $this->createJWT($jwtPayload, $keyData['private_key']);

        // Access Token 요청
        $response = (new Client())->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'];
    }

    private function createJWT(array $payload, string $privateKey): string {
        $headers = ['alg' => 'RS256', 'typ' => 'JWT'];
        $base64Headers = $this->base64UrlEncode(json_encode($headers));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        openssl_sign("{$base64Headers}.{$base64Payload}", $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $base64Signature = $this->base64UrlEncode($signature);

        return "{$base64Headers}.{$base64Payload}.{$base64Signature}";
    }

    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function collection(string $name): FirestoreCollection {
        return new FirestoreCollection($this->http, "{$this->baseUrl}/{$name}", $this->token);
    }
}

class FirestoreCollection {
    private Client $http;
    private string $collectionUrl;
    private string $parent;      // .../documents
    private string $collectionId; // bujos
    private string $token;

    public function __construct(Client $http, string $collectionUrl, string $token) {
        $this->http = $http;
        $this->collectionUrl = $collectionUrl;
        $this->token = $token;
        $this->collectionId = basename($collectionUrl);
        $this->parent = dirname($collectionUrl);
    }

    public function add(array $data): array {
        $response = $this->http->post($this->collectionUrl, [
            'json' => ['fields' => $this->encodeFields($data)],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function get(string $id): ?array {
        try {
            $response = $this->http->get("{$this->collectionUrl}/{$id}");
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->decodeDocument($data);
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    public function update(string $id, array $data): array {
        // Firestore API 는 updateMask 를 쿼리 파라미터로 요구함
        // 형식: ?updateMask.fieldPaths=field1&updateMask.fieldPaths=field2
        $maskParams = [];
        foreach (array_keys($data) as $field) {
            $maskParams[] = "updateMask.fieldPaths=" . urlencode($field);
        }
        $maskQuery = implode('&', $maskParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->collectionUrl}/{$id}?{$maskQuery}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['fields' => $this->encodeFields($data)]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \Exception("Firestore update failed: {$response}");
        }

        return json_decode($response, true);
    }

    public function delete(string $id): void {
        $this->http->delete("{$this->collectionUrl}/{$id}");
    }

    public function where(array $conditions): array {
        // 간단한 where 구현 (동등 비교만)
        $filters = [];
        foreach ($conditions as $field => $value) {
            $filters[] = [
                'fieldFilter' => [
                    'field' => ['fieldPath' => $field],
                    'op' => 'EQUAL',
                    'value' => $this->encodeValue($value),
                ],
            ];
        }

        $filter = count($filters) === 1 ? $filters[0] : ['compositeFilter' => ['op' => 'AND', 'filters' => $filters]];

        $response = $this->http->post("{$this->parent}:runQuery", [
            'json' => [
                'structuredQuery' => [
                    'from' => [
                        ['collectionId' => $this->collectionId]
                    ],
                    'where' => $filter
                ]
            ],
        ]);

        $results = json_decode($response->getBody()->getContents(), true);
        $docs = [];
        foreach ($results ?? [] as $result) {
            if (isset($result['document'])) {
                $docs[] = $this->decodeDocument($result['document']);
            }
        }
        return $docs;
    }

    public function getAll(): array {
        $docs = [];
        $pageToken = '';

        do {
            $url = $this->collectionUrl . '?pageSize=100';
            if ($pageToken) {
                $url .= '&pageToken=' . urlencode($pageToken);
            }

            $response = $this->http->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            foreach ($data['documents'] ?? [] as $doc) {
                $docs[] = $this->decodeDocument($doc);
            }

            $pageToken = $data['nextPageToken'] ?? '';
        } while ($pageToken);

        return $docs;
    }

    private function encodeFields(array $data): array {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeValue($value);
        }
        return $fields;
    }

    private function encodeValue($value): array {
        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }
        if (is_int($value)) {
            return ['integerValue' => $value];
        }
        if (is_string($value)) {
            return ['stringValue' => $value];
        }
        if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
            return ['timestampValue' => $value->format('Y-m-d\TH:i:s.u\Z')];
        }
        if ($value === null) {
            return ['nullValue' => 'NULL_VALUE'];
        }
        return ['stringValue' => (string)$value];
    }

    private function decodeDocument(?array $doc): ?array {
        if (!$doc || !isset($doc['fields'])) {
            return null;
        }

        $result = [];
        foreach ($doc['fields'] as $key => $field) {
            $result[$key] = $this->decodeValue($field);
        }

        // Firestore 문서 ID 추출
        if (isset($doc['name'])) {
            $result['id'] = basename($doc['name']);
        }

        return $result;
    }

    private function decodeValue(array $field) {
        if (isset($field['booleanValue'])) {
            return $field['booleanValue'];
        }
        if (isset($field['integerValue'])) {
            return (int)$field['integerValue'];
        }
        if (isset($field['stringValue'])) {
            return $field['stringValue'];
        }
        if (isset($field['timestampValue'])) {
            return new DateTime($field['timestampValue']);
        }
        if (isset($field['nullValue'])) {
            return null;
        }
        return $field;
    }
}

// 클라이언트 인스턴스 생성
$firestoreClient = null;

function db(): FirestoreClient {
    global $firestoreClient;
    if ($firestoreClient === null) {
        $firestoreClient = new FirestoreClient();
    }
    return $firestoreClient;
}
