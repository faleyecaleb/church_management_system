# Church Management System - Architecture & Progress Overview

This document serves as a simple, plain-English guide to understanding the core features, logic, and progress made on the Church Management System.

---

## 1. Multi-Tenancy (The "Branch" System)
**Goal:** Run the Adult Church, Youth Church, and Children Church from a single application without their data mixing.

*   **How it works:** Every major database table (Members, Attendances, Finances, etc.) has a `church_id` column. 
*   **The Magic (ChurchScope):** Whenever a branch Admin logs in, the system automatically attaches their `church_id` to every single database query. They are physically locked out of seeing other branches' data.
*   **Super Admin:** The global Super Admin has a `church_id` of `null`. The system recognizes this and allows them to see everything. They use a dropdown in the top-right corner to "switch" into different branches temporarily.

## 2. Dynamic UI Themes
**Goal:** Give each branch a completely distinct visual identity while sharing the same underlying features.

*   **How it works:** A background script (`ThemeMiddleware`) checks which branch the logged-in user belongs to.
*   **The Layouts:** Based on the branch, it loads a completely different CSS layout:
    *   **Adult Church:** Professional, classic blue and slate gradient.
    *   **Youth Church:** Sleek, modern deep indigo gradient.
    *   **Children Church:** Warm, friendly, deep teal gradient.
*   *Note: If the Super Admin switches branches, the entire UI changes colors instantly to match the branch they are viewing.*

## 3. Roles & Access Control
**Goal:** Ensure staff only see what they are supposed to see.

We moved away from hardcoded roles to a dynamic **Permissions System**.
*   **Key Roles Added:**
    *   `admin`: Complete control over a specific branch.
    *   `curate_pastor`: Second in command, specifically manages church workers.
    *   `attendance_manager`: Handles general check-ins but lacks access to sensitive pastoral data.
    *   `pa`: Personal Assistant handling bookings.

## 4. The Attendance Flow (General vs. Workers)
**Goal:** Keep worker attendance integrated with general attendance, but give the Curate Pastor a special tool for service allocations.

*   **General Check-in:** The `attendance_manager` sees *everyone* (Members, Choir, Ushers, etc.) on the normal check-in screen. They simply mark people present. There is no special "Worker" label to confuse the general process.
*   **Worker Allocation:** The `curate_pastor` has a special permission (`attendance.view_worker`). This unlocks a dedicated menu item called **"Worker Allocation"**.
*   **The Allocation Page:** This dedicated page automatically isolates only the people who checked in today *and* belong to a Department (e.g., Media, Choir). It groups them clearly so the pastor can assign them duties.

## 5. Fast Check-In & Attendance Analytics
**Goal:** Handle check-ins for members without smartphones and provide deep analytics on member punctuality and regularity.

*   **Scanner Interface (`/attendance/scanner`):** A high-speed, keyboard-emulation interface. It automatically refocuses on the input box. A hardware scanner can read a barcode/fingerprint (mapped to `unique_id` in the DB), or an admin can manually type a phone number and press Enter to instantly check someone in without reloading the page.
*   **Analytics Dashboard (`/attendance/analytics`):**
    *   **Most Regular:** Calculates total attendances over dynamic rolling periods (30/90/365 days) or specific calendar months.
    *   **Most Punctual:** Calculates the exact minute difference between `check_in_time` and the service's scheduled `start_time` to find the average minutes early/late.
    *   **Exports & Visuals:** Features Chart.js animated bar graphs and a one-click direct Excel export.

## 6. Member Import & Mobile App Prep
**Goal:** Allow bulk uploading of members via Excel/CSV and prepare their accounts for a future mobile app.

*   **Upload Fixes:** The bulk upload UI was enhanced with loading spinners and robust error catching to handle bad files (like hidden Excel characters) without crashing the browser.
*   **Multi-Tenant Uploads:** When a branch Admin uploads an Excel file of 1,000 members, the system automatically assigns all 1,000 members to that specific Admin's branch.
*   **Mobile App Passwords:** We added a `password` column to the Members database. Now, during bulk upload, the system automatically takes the member's **Surname (Last Name)**, converts it to lowercase, securely encrypts it, and saves it as their default password. *(e.g., John Doe's mobile app password is automatically set to "doe").*

## 7. Counselling Booking System (Admin Side)
**Goal:** Allow the Personal Assistant to manage incoming counselling requests from the future mobile app.

*   **Database Foundation:** The `counselling_bookings` table tracks the `member_id`, `church_id` (for branch isolation), `requested_date`, `requested_time`, `reason`, and `status` (pending/approved/rejected).
*   **Strict Access Control:** The Dashboard is strictly limited. The Super Admin can view the list, but **only the PA** (who holds the `counselling.manage` permission) is presented with the buttons to physically Approve or Reject the requests.

---

## 📱 Future Mobile App API Requirements

When building the mobile app, the developers will need to construct the following API endpoints to hook into the backend we just built:

**1. Authentication:**
*   **Login Endpoint:** The app must post to `/api/v1/login` using the `email` and the `password` (which defaults to their lowercase surname if imported via CSV). 
*   *Note: Laravel Sanctum is already installed in the backend to issue API tokens upon successful login.*

**2. Counselling Booking Request:**
*   **Endpoint Needed:** `POST /api/v1/counselling/book`
*   **Payload Required:** 
    *   `requested_date` (Format: YYYY-MM-DD)
    *   `requested_time` (Format: HH:MM)
    *   `reason` (Text string)
*   **Backend Handling:** The API controller should extract the authenticated `member_id` from the Sanctum token, extract their `church_id`, merge it with the payload, and create a `CounsellingBooking` record with a default status of `pending`.

**3. Attendance QR Generation:**
*   **Endpoint Needed:** The app needs to hit an endpoint that returns a QR code payload containing the member's `unique_id` or `id`, which can then be displayed on their phone screen to be scanned by the church ushers using the Fast Scanner interface.

---

## 📋 Active Test Accounts

You can use these accounts to test the isolation and themes. The password for **all** accounts is `password123` (except the Super Admin).

| Role | Email | Password | Notes |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@church.com` | `admin123` | Global view. Use the top right dropdown to switch themes/branches. |
| **Adult Admin** | `adult_admin@church.com` | `password123` | Locked to Adult Church. Classic Blue theme. |
| **Youth Admin** | `youth_admin@church.com` | `password123` | Locked to Youth Church. Indigo theme. |
| **Children Admin** | `children_admin@church.com` | `password123` | Locked to Children Church. Teal theme. |
| **Youth Curate** | `youth_curate@church.com` | `password123` | Youth Church. Can see the "Worker Allocation" menu. |
| **Youth Attendance** | `youth_attendance@church.com` | `password123` | Youth Church. *Cannot* see the "Worker Allocation" menu. |
| **Adult PA** | `adult_pa@church.com` | `password123` | Adult Church. *Only* account authorized to click Approve/Reject on bookings. |

---
*Documented on: May 6, 2026. End of Phase 4 Implementation.*