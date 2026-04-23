# PRD: Firebase-based Bujo Management Web App

## 1. Project Overview
The goal of this project is to develop a web application using PHP that allows users to manage "Bujo" data stored in Google Cloud Firestore. The app will provide full CRUD (Create, Read, Update, Delete) capabilities and a statistics dashboard categorized by Bujo categories.

## 2. Target Users
- Administrators or users who need to manage Bujo records and analyze distribution/statistics across different categories.

## 3. Functional Requirements

### 3.1 Data Management (CRUD)
- **Create**: Add new Bujo records and Categories.
- **Read**: 
    - List all Bujo records with pagination/filtering.
    - View detailed information of a single Bujo record.
    - List all categories.
- **Update**: Edit existing Bujo records and Category details.
- **Delete**: Remove Bujo records or Categories.

### 3.2 Statistics & Analytics
- **Category-based Statistics**: 
    - Calculate the total number of Bujo records per category.
    - Calculate the sum of `account` (amount) per category.
    - Visualize the distribution of Bujo records across categories (e.g., via a table or simple chart).

### 3.3 Data Integration
- Integration with **Google Cloud Firestore** using the Firebase Admin SDK for PHP.
- Mapping of fields as defined in `구성.md`.

## 4. Data Model (Based on `구성.md`)

### 4.1 `bujo_categories` Collection
| Field | Type | Description |
|---|---|---|
| `name` | String | Category name (e.g., "동기") |
| `description` | String | Category description |
| `createdAt` | Timestamp | Creation date |

### 4.2 `bujos` Collection
| Field | Type | Description |
|---|---|---|
| `name` | String | Person's name |
| `account` | Int64 | Amount/Account value |
| `dDay` | Timestamp | Target date/Event date |
| `reason` | String | Reason for the record |
| `etc` | String | Additional notes |
| `isBujo` | Boolean | Bujo status flag |
| `groupName` | String/Null | Associated group name |
| `createdAt` | Timestamp | Creation date |
| `categoryId` | String | Reference to `bujo_categories` (Implicitly needed for stats) |

## 5. Technical Stack
- **Backend**: PHP (8.x recommended)
- **Database**: Google Cloud Firestore
- **SDK**: `google/cloud-firestore` (Firebase Admin SDK for PHP)
- **Frontend**: HTML5, CSS3 (Bootstrap for rapid UI development), JavaScript

## 6. User Interface (UI) Requirements
- **Dashboard**: Summary of total records and a high-level statistics view.
- **Bujo List Page**: A table showing all Bujo records with search and filter options.
- **Bujo Form Page**: A form to create or edit a Bujo record.
- **Category Management Page**: A list and form to manage categories.
- **Statistics Page**: Detailed breakdown of data by category.

## 7. Non-Functional Requirements
- **Security**: Secure handling of Firebase Service Account keys.
- **Performance**: Efficient querying of Firestore to minimize read/write costs.
- **Usability**: Simple and intuitive interface for data entry and viewing.
