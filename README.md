# APRecycle - Smart Recycling System

## 1. Project Overview
APRecycle is a web-based smart recycling system designed to encourage sustainable habits through gamification and AI technology.

**Key Features:**
* **AI Bin Camera:** Automatically classifies waste items using Google Gemini AI.
* **Recycling Dashboard:** Gamified experience for users to earn points and badges.
* **Management Portals:** Dedicated dashboards for Administrators and Eco-Moderators.

---

## 2. Installation & Setup

### A. Folder Setup
1.  Ensure **WAMP Server** is installed and running.
2.  Move the project folder into your WAMP web directory:
    * **Path:** `C:\wamp64\www\APRecycle\`

### B. Database Import
1.  Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2.  Create a new database named **`aprecycle`** (Collation: utf8mb4_0900_ai_ci) (Must match the name in `php/config.php`).
3.  Select the `aprecycle` database.
4.  Click **Import**.
5.  Choose the SQL file located in this project at:
    * `database/aprecycle.sql`
6.  Click **Go**.

### C. Verify Database Connection
The system is pre-configured for standard WAMP settings. You can verify this in `php/config.php`:
* **Host:** `localhost`
* **User:** `root`
* **Password:** *(empty)*

---

## 3. API Configuration (Critical)
*The Bin Camera requires a valid API Key to function.*

1.  Navigate to the project root: `C:\wamp64\www\APRecycle\`
2.  Create a new file named **`.env`**.
3.  Open the file and add your Gemini API Key in the following format:
    ```
    GEMINI_API_KEY=your_actual_api_key_here
    ```
    *(See `SETUP_GUIDE.md` or `.env.example` for more details).*

---

## 4. Test Credentials

**Master Password for ALL Accounts:** `password123`

### 🛡️ Administrator (Full Access)
* **Username:** `admin1`
* **Username:** `admin2`

### 🌿 Eco-Moderator (Content Management)
* **Username:** `moderator1`
* **Username:** `moderator2`

### ♻️ Recycler (Standard User)
* **Username:** `sarah_green` (Recommended for testing)
* **Username:** `emma_legend`

---

## 5. How to Run
1.  Open your browser (Chrome recommended).
2.  **Home Page:** `http://localhost/APRecycle/`
3.  **Bin Camera Test:** `http://localhost/APRecycle/bin_camera/bin_camera.php`

---

## 6. Troubleshooting

* **Database Error:** Ensure the database name in phpMyAdmin is exactly `aprecycle`.
* **Camera Not Working:** Ensure you are accessing the site via `localhost` (not an IP address) and have allowed browser camera permissions.
* **API Error:** Check that the `.env` file is in the root directory and contains a valid key without extra spaces.