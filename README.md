# APRecycle: Smart and Gamified Recycling System for APU Campus

A comprehensive web application designed to promote sustainable recycling practices on the APU campus through gamification, AI-powered waste classification, and competitive team challenges.

---

## 📋 Project Information

**Course:** AAPP012-4-2-RWDD (Responsive Web Design & Development)  
**Institution:** Asia Pacific University of Technology & Innovation  
**Sustainability Theme:** Recycling & Upcycling  
**Intake:** UCDF2407ICT(DI)  
**Project Duration:** Week 1 - Week 14 (September 2025 - January 2026)

---

## 👥 Team Members

| Student ID | Name | Intake |
|------------|------|--------|
| TP081786 | HENG EE SERN | UCDF2407ICT(DI) |
| TP083604 | LAEU ZI-LI | UCDF2407ICT(DI) |
| TP082620 | LOW ZE XUAN | UCDF2407ICT(DI) |
| TP082730 | MUHAMMAD FARRIS BIN RAZMAN | UCDF2407ICT(DI) |
| TP080852 | TAN HAO SHUAN | UCDF2407ICT(DI) |

---

## 🎯 System Overview

APRecycle is a three-tier recycling management system that combines:
- **Smart Bin Technology** with camera integration and QR code scanning
- **AI-Powered Material Recognition** using Gemini API 2.5
- **Gamification Elements** including points, badges, streaks, and team competitions
- **Educational Resources** for proper recycling practices
- **Analytics Dashboard** for tracking environmental impact

---

## 📊 Complete Task Distribution by Feature

### 🔵 RECYCLER ROLE

#### Recycler Dashboard
**Assigned To:** TAN HAO SHUAN  
**Database Tables:** USER, RECYCLING_SUBMISSION, USER_BADGE  
**Features:**
- Display user statistics (total points, CO₂ reduced, current streak, badges earned)
- Show recycling streak calendar
- Quick action buttons (Recycle Now, Education Hub, Join Challenge, My Badges)
- Recent activity feed

#### Inbox - Message List
**Assigned To:** TAN HAO SHUAN  
**Database Tables:** RECYCLING_SUBMISSION, EDUCATIONAL_CONTENT  
**Features:**
- Display feedback from moderators
- Show new educational content notifications
- Unread message counter
- Message categorization

#### Inbox - Message Details
**Assigned To:** TAN HAO SHUAN  
**Database Tables:** RECYCLING_SUBMISSION  
**Features:**
- Display full feedback message
- Show related submission details
- Mark as read functionality
- Delete message option

#### Team Main Menu
**Assigned To:** MUHAMMAD FARRIS  
**Database Tables:** USER  
**Features:**
- Display current team status
- Options to join existing team or create new team
- Team enrollment verification

#### Team Creation
**Assigned To:** MUHAMMAD FARRIS  
**Database Tables:** USER, TEAM  
**Features:**
- Team name input
- Team description input
- Create team functionality
- Update user's team_id in system

#### Team Joining
**Assigned To:** MUHAMMAD FARRIS  
**Database Tables:** USER, TEAM  
**Features:**
- Display available teams with search functionality
- Show team details (members, leader, member count)
- Join selected team button
- Update user's data in system

#### Leaderboard Main Menu
**Assigned To:** LAEU ZI-LI  
**Database Tables:** None (navigation only)  
**Features:**
- Navigate to Individual Points leaderboard
- Navigate to Team Points leaderboard
- Navigate to Challenge Points leaderboard

#### Leaderboard - Individual Points
**Assigned To:** LAEU ZI-LI  
**Database Tables:** USER, RECYCLING_SUBMISSION  
**Features:**
- Display monthly and lifetime rankings
- Show user rank, points, and items recycled
- Sortable by time period

#### Leaderboard - Team Points
**Assigned To:** LAEU ZI-LI   
**Database Tables:** TEAM, USER_CHALLENGE, CHALLENGE  
**Features:**
- Challenge selection dropdown
- Display challenge details (duration, multiplier)
- Show team rankings with members list
- Display total plastic items collected per team

#### Educational Content
**Assigned To:** MUHAMMAD FARRIS  
**Database Tables:** EDUCATIONAL_CONTENT  
**Features:**
- Search functionality
- Category filter buttons (All, Plastic, Paper, Metal, E-Waste, General Tips)
- Display featured articles with thumbnails
- Show article metadata (publish date, read time, views, category)

#### Challenge Main Menu
**Assigned To:** LOW ZE XUAN  
**Database Tables:** CHALLENGE  
**Features:**
- Toggle between Available and My Challenges
- Display challenge cards with details
- Show challenge status (Active, Upcoming)
- View Details button for each challenge

#### My Challenge
**Assigned To:** LOW ZE XUAN  
**Database Tables:** USER_CHALLENGE, CHALLENGE  
**Features:**
- Display challenge statistics (active, completed, bonus points)
- Show active challenges with progress bars
- Display time remaining and items remaining
- View Details option

#### Profile Management
**Assigned To:** HENG EE SERN  
**Database Tables:** USER  
**Features:**
- Display current profile information
- Update profile form
- Save changes to database

---

### 🟢 ECO-MODERATOR ROLE

#### Eco-Moderator Dashboard
**Assigned To:** LOW ZE XUAN  
**Database Tables:** RECYCLING_SUBMISSION, USER, EDUCATIONAL_CONTENT  
**Features:**
- Display pending reviews count
- Show total reviewed count
- Display educational posts count
- Quick access to Review AI Classifications and Create Educational Content

#### Review Uncertain AI Classifications
**Assigned To:** LOW ZE XUAN  
**Database Tables:** RECYCLING_SUBMISSION, SUBMISSION_MATERIAL, USER, REWARD  
**Features:**
- Display pending reviews count
- Show submission details (user, timestamp, AI prediction, confidence level)
- Display uploaded image
- Material type and condition fields
- Decision dropdown (Is this recyclable waste?)
- Feedback text area
- Points to award input
- Approve & Send Feedback / Reject & Send Guidance buttons

#### Educational Content Library
**Assigned To:** MUHAMMAD FARRIS  
**Database Tables:** EDUCATIONAL_CONTENT, USER  
**Features:**
- Display published content with Edit/Delete options
- Show drafts with Continue Editing/Delete options
- Create New button
- Content metadata (published date, views, category)

#### Create Educational Content
**Assigned To:** MUHAMMAD FARRIS  
**Database Tables:** EDUCATIONAL_CONTENT, USER  
**Features:**
- Content title input
- Category dropdown
- Content body text area
- Image upload (optional)
- Tags input (comma separated)
- Publish Content button

---

### 🔴 ADMINISTRATOR ROLE

#### Administrator Dashboard
**Assigned To:** HENG EE SERN  
**Database Tables:** USER, CHALLENGE, SUBMISSION_MATERIAL  
**Features:**
- Display total users count
- Show active challenges count
- Display items recycled count
- Show eco-moderators count
- Quick access cards to Challenges Management, System Analytics, and Eco-Moderator Management

#### Eco-Moderator Management - Main Page
**Assigned To:** TAN HAO SHUAN 
**Database Tables:** USER  
**Features:**
- Display active eco-moderators list
- Show moderator details (ID, joined date, reviews completed)
- Edit and Remove buttons for each moderator
- Add New Moderator button

#### Add Eco-Moderator
**Assigned To:** TAN HAO SHUAN   
**Database Tables:** USER  
**Features:**
- Student/Staff ID input
- Full name input
- Email address input
- Phone number input
- Add Eco-Moderator button

#### Update Eco-Moderator
**Assigned To:** TAN HAO SHUAN   
**Database Tables:** USER  
**Features:**
- Pre-filled form with current moderator information
- Editable fields (Student/Staff ID, Full Name, Email, Phone)
- Update Information button

#### System Analytics Dashboard
**Assigned To:** HENG EE SERN  
**Database Tables:** USER, RECYCLING_SUBMISSION, MATERIAL, USER_CHALLENGE  
**Features:**
- Display total items recycled
- Show active users count
- Display total CO₂ reduced
- Show participation rate
- Material category breakdown (chart and list)
- Participation trends chart
- Environmental impact calculations
- Top 5 contributors (users or teams) with items/points
- Generate Detailed Report (PDF) button

#### Challenge Management - Main Page
**Assigned To:** LOW ZE XUAN  
**Database Tables:** CHALLENGE  
**Features:**
- Display active challenges with Edit/Delete buttons
- Show upcoming challenges with Edit/Delete buttons
- Challenge details (start/end dates, multiplier, participants)
- Create New Challenge button

#### Create Challenge
**Assigned To:** LOW ZE XUAN  
**Database Tables:** CHALLENGE, REWARD, TEAM, MATERIAL  
**Features:**
- Challenge title input
- Challenge description text area
- Start date picker
- End date picker
- Point multiplier input
- Target material type dropdown
- Badges dropdown (select from existing)
- Rewards dropdown (select from existing)
- Create Challenge button

#### Leaderboard Overview
**Assigned To:** LAEU ZI-LI 
**Database Tables:** USER, CHALLENGE, TEAM  
**Features:**
- Display total recyclers count
- Show active this month count
- Display total teams count
- Show active challenges count
- Quick access cards to Individual Rankings, Team Rankings, and Challenge Rankings
- Top individual recyclers display (top 3 with points and items recycled)

---

## 🎨 Wireframe Coverage by Team Member

### TAN HAO SHUAN
- Recycler Dashboard (Desktop & Mobile)
- Inbox Message List (Desktop & Mobile)
- Inbox Details (Desktop & Mobile)
- Personal Dashboard (from Main Page flowchart)
- Inbox (Feedback from Eco-Moderators)
- Eco-Moderator Management (from flowchart)
- Administrator Eco-Moderator Management Main Page (Desktop & Mobile)
- Administrator Add Eco-Moderator (Desktop & Mobile)
- Administrator Update Eco-Moderator (Desktop & Mobile)

### MUHAMMAD FARRIS
- Team Main Menu (Desktop & Mobile)
- Team Creation (Desktop & Mobile)
- Team Joining (Desktop & Mobile)
- Educational Content (Desktop & Mobile)
- Challenge Participation (from flowchart)
- Create Educational Content (from flowchart)
- Eco-Moderator Educational Content Library (Desktop & Mobile)
- Eco-Moderator Create Educational Content (Desktop & Mobile)

### LOW ZE XUAN
- Challenge Main Menu (Desktop & Mobile)
- My Challenge (Desktop & Mobile)
- Eco-Moderator Review Uncertain AI Classifications (Desktop & Mobile)
- Administrator Challenge Management (Desktop & Mobile)
- Administrator Create Challenge (Desktop & Mobile)
- Recycling Process (flowchart)
- Review Uncertain AI Classifications (from flowchart)
- Challenge Creation & Management (from flowchart)

### HENG EE SERN
- Administrator Dashboard (Desktop & Mobile)
- Administrator System Analytics (Desktop & Mobile)
- Authentication Process (flowchart)
- Main Page of Recycler (flowchart)
- Profile Management (flowchart)
- Main Page of Administrator (flowchart)
- System Analytics Dashboard (from flowchart)
- Point System Configuration (from Admin role)

### LAEU ZI-LI
- Eco-Moderator Dashboard (Desktop & Mobile)
- Main Page of Eco Moderator (flowchart)
- Environmental Impact Metrics & Data (from Recycler role)
- Educational Feedback System (from Eco-Moderator role)
- Report Exportation (from Admin role)
- Leaderboard Main Menu (Desktop & Mobile)
- Leaderboard Individual Points (Desktop & Mobile)
- Leaderboard Team Points (Desktop & Mobile)
- Leaderboard & Rankings (from Main Page flowchart)
- Administrator Leaderboard Overview (Desktop & Mobile)

---

## ✨ Unique Functionalities

### 1. Smart Bin Camera Integration
**Description:** Physical recycling bins equipped with cameras  
**Function:** Captures images of recycled items linked to user's QR code  
**Benefit:** Ensures authenticity and accurate record-keeping  
**Technology:** Camera module + QR code scanner

### 2. AI-Based Material Recognition
**Description:** Automated waste classification using Gemini API 2.5  
**Function:** Identifies material type and quantity from images  
**Confidence Threshold:** < 80% flagged for human review  
**Benefit:** Prevents cheating and estimates carbon reduction

### 3. Hybrid AI-Human Moderation Workflow
**Description:** Two-tier verification system  
**Function:** AI handles high-confidence cases, humans review uncertain ones  
**Benefit:** Maintains data integrity and provides educational feedback

### 4. Dynamic Gamification Engine
**Points System:**
- Active Points: Used for challenges and competitions
- Lifetime Points: Permanent record of contributions

**Features:** Team competitions, leaderboards, streaks, badges  
**Benefit:** Increases engagement through healthy competition

### 5. Personal Impact Dashboard
**Metrics Tracked:**
- Total points earned
- CO₂ reduction contribution
- Current streak days
- Badges unlocked
- Items recycled (by category)

**Benefit:** Provides motivation and visualizes individual impact

### 6. Real-Time Event Analytics and Reports
**Capabilities:**
- Monitor ongoing activities
- Generate trend analysis
- Track participation rates
- Material category breakdowns

**Benefit:** Enables data-driven sustainability planning

### 7. Recycling Knowledge Hub
**Content Types:**
- Step-by-step recycling guides
- Material identification tips
- Campus recycling policies
- Environmental impact facts

**Benefit:** Educates users for maximum recycling effectiveness

---

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup and structure
- **CSS3** - Responsive styling and animations
- **JavaScript** - Client-side interactivity and validation

### Backend
- **PHP** - Server-side scripting and business logic
- **MySQL** - Relational database management

### External APIs
- **Gemini API 2.5** - AI-powered image recognition and material classification

### Design Principles
- **Responsive Design** - Mobile-first approach, works on all devices
- **Accessibility** - WCAG compliance for inclusive usage
- **User Experience** - Intuitive navigation and clear feedback

---

## 🗄️ Database Structure

### Core Tables

**user**
- user_id (PK)
- username, password, email
- role (recycler/eco_moderator/administrator)
- qr_code
- lifetime_points
- created_at
- team_id (FK)

**recycling_submission**
- submission_id (PK)
- user_id (FK), bin_id (FK)
- image_url
- ai_confidence (decimal 5,2)
- status (varchar 20)
- moderator_feedback (text)
- created_at

**material**
- material_id (PK)
- material_name (varchar 100)
- points_per_item (int)

**submission_material**
- submission_id (FK), material_id (FK) - Composite PK
- quantity (int)

**challenge**
- challenge_id (PK)
- title (varchar 100)
- description (text)
- start_date, end_date
- badge_id (FK), reward_id (FK)
- point_multiplier (int)

**team**
- team_id (PK)
- team_name (varchar 100) - UNIQUE
- description (text)
- date_created
- points (int, default 0)

**badge**
- badge_id (PK)
- badge_name (varchar 100)
- points_required (int)
- description (text)

**reward**
- reward_id (PK)
- reward_name (varchar 100)
- points_required (int)
- description (text)

**educational_content**
- content_id (PK)
- title (varchar 50)
- content_body (text)
- image (varchar 255)
- tags (varchar 255)
- created_at
- author_id (FK)

**recycling_bin**
- bin_id (PK)
- bin_name (varchar 50)
- bin_location (varchar 50)

### Junction Tables

**user_badge**
- user_id (FK), badge_id (FK) - Composite PK
- date_awarded

**user_challenge**
- user_id (FK), challenge_id (FK) - Composite PK
- challenge_point (int, default 0)
- date_joined

**user_reward**
- user_id (FK), reward_id (FK) - Composite PK
- date_earned

---

## 📅 Project Timeline

| Week | Milestone | Deliverable | Due Date |
|------|-----------|-------------|----------|
| 1 | Group Formation & Theme Selection | Team formed | Week 1 |
| 2-3 | Proposal Preparation & Submission | Project Proposal | 26 Oct 2025 |
| 4-5 | Requirement Gathering & Analysis | Requirements document | - |
| 6-8 | Preliminary Draft | ERD, Flowchart, Wireframe, Data Dictionary, Navigation Map | 23 Nov 2025 |
| 9 | Database & PHP Setup | Functional database | - |
| 10-11 | Core Features Implementation | CRUD operations, role-based features | - |
| 12 | Interactivity & Responsive Design | JavaScript features, responsive design | - |
| 13 | Testing & Debugging | Bug fixes, optimization | - |
| 13 | Final Report Preparation & Submission | Complete documentation | 18 Jan 2026 |
| 14 | Project Presentation & Demo | 30-minute demo via MS Teams | 19 Jan 2026+ |

---

## 📚 Course Learning Outcomes

**CLO1:** Demonstrate the principles and technologies of the Internet and World Wide Web (C3, PLO2)
- **Documentation Component**
- Implementation of client-server architecture
- Understanding of HTTP protocols and web standards
- Application of responsive web design principles

**CLO2:** Construct an effective and interactive web application using scripting languages (P3, PLO3)
- **Implementation Component**
- Client-side scripting with JavaScript
- Server-side scripting with PHP
- Database integration with MySQL
- API integration (Gemini API 2.5)

---

## 📋 Deliverables

### 1. Proposal (Due: 26 October 2025) ✅
- ✅ Project title
- ✅ Three user roles with functionalities
- ✅ Group member task assignments
- ✅ Unique sustainability-themed functionalities

**Submitted:** Project title, user roles, task distribution, and unique features documented

---

### 2. Preliminary Draft (Due: 23 November 2025) ✅

**Required Documents:**

#### Flowchart
- **Recycling Process** - LOW ZE XUAN
- **Authentication Process** - HENG EE SERN
- **Recycler Main Page** - TAN HAO SHUAN
- **Recycler Profile Management** - HENG EE SERN
- **Recycler Challenge Participation** - MUHAMMAD FARRIS
- **Recycler Team Creation and Joining** - MUHAMMAD FARRIS
- **Recycler Personal Dashboard** - TAN HAO SHUAN
- **Recycler Leaderboard and Rankings** - LOW ZE XUAN
- **Recycler Inbox** - TAN HAO SHUAN
- **Eco-Moderator Main Page** - LAEU ZI-LI
- **Eco-Moderator Review Uncertain AI Classifications** - LOW ZE XUAN
- **Eco-Moderator Create Educational Content** - MUHAMMAD FARRIS
- **Administrator Main Page** - HENG EE SERN
- **Administrator Challenge Creation & Management** - LOW ZE XUAN
- **Administrator System Analytics Dashboard** - HENG EE SERN
- **Administrator Eco-Moderator Management** - MUHAMMAD FARRIS

#### Entity Relationship Diagram (ERD)
- **Assigned To:** HENG EE SERN
- Shows all 15 tables with relationships
- Normalized database structure
- Primary and foreign key relationships

#### Data Dictionary
- **Assigned To:** HENG EE SERN
- Complete table descriptions
- Field definitions with data types and lengths
- Relationship documentation
- Sample data examples

#### Wireframes
**Recycler Wireframes:**
- Recycler Dashboard (Desktop & Mobile) - TAN HAO SHUAN
- Inbox Message List (Desktop & Mobile) - TAN HAO SHUAN
- Inbox Details (Desktop & Mobile) - TAN HAO SHUAN
- Team Main Menu (Desktop & Mobile) - MUHAMMAD FARRIS
- Team Creation (Desktop & Mobile) - MUHAMMAD FARRIS
- Team Joining (Desktop & Mobile) - MUHAMMAD FARRIS
- Leaderboard Main Menu (Desktop & Mobile) - LAEU ZI-LI
- Leaderboard Individual Points (Desktop & Mobile) - LAEU ZI-LI
- Leaderboard Team Points (Desktop & Mobile) - LAEU ZI-LI
- Educational Content (Desktop & Mobile) - MUHAMMAD FARRIS
- Challenge Main Menu (Desktop & Mobile) - LOW ZE XUAN
- My Challenge (Desktop & Mobile) - LOW ZE XUAN

**Eco-Moderator Wireframes:**
- Eco-Moderator Dashboard (Desktop & Mobile) - LAEU ZI-LI
- Review Uncertain AI Classifications (Desktop & Mobile) - LOW ZE XUAN
- Educational Content Library (Desktop & Mobile) - MUHAMMAD FARRIS
- Create Educational Content (Desktop & Mobile) - MUHAMMAD FARRIS

**Administrator Wireframes:**
- Administrator Dashboard (Desktop & Mobile) - HENG EE SERN
- Eco-Moderator Management Main Page (Desktop & Mobile) - TAN HAO SHUAN
- Add Eco-Moderator (Desktop & Mobile) - MTAN HAO SHUAN
- Update Eco-Moderator (Desktop & Mobile) - TAN HAO SHUAN
- System Analytics Dashboard (Desktop & Mobile) - HENG EE SERN
- Challenge Management (Desktop & Mobile) - LOW ZE XUAN
- Create Challenge (Desktop & Mobile) - LOW ZE XUAN
- Leaderboard Overview (Desktop & Mobile) - LAEU ZI-LI

#### Navigation Map (Site Map)
- **Assigned To:** ALL MEMBERS
- Complete site structure
- User role navigation paths
- Page relationships and hierarchy

**Status:** All components completed and submitted

---

### 3. Final Report (Due: 18 January 2026)

**Format Requirements:**
- Typeface: Times New Roman (bold, italic, underline for emphasis)
- Font size: 12pt (except titles and headings)
- Spacing: 1.5 lines
- Alignment: Justified
- All pages numbered
- Headers/footers may be used

**Cover Page Must Include:**
- Group Member Name & Student ID
- Intake Code
- Subject
- Project Title

**Required Content:**

#### a) Table of Contents
- With page numbers
- All sections and subsections

#### b) Introduction and Project Plan
- **Objectives:** Purpose of APRecycle system
- **Scope:** Features and limitations
- **Intended Users:** Recyclers, Eco-Moderators, Administrators
- **Gantt Chart:** Project timeline (14 weeks)

#### c) Design
- **Final Flowchart:** All 16 flowcharts (system processes)
- **ERD:** Complete entity relationship diagram
- **Data Dictionary:** All 15 tables documented
- **Wireframe:** All 72 wireframes (main pages, desktop & mobile)
- **Navigation Map:** Complete site structure

#### d) Implementation
- **Steps:** Development process explanation
- **Code Snippets:** At least 2 per member (10+ total)
  - HENG EE SERN: Authentication, Profile Management
  - LAEU ZI-LI: Eco-Moderator Dashboard, Analytics
  - LOW ZE XUAN: Leaderboard, AI Review, Challenges
  - MUHAMMAD FARRIS: Team Management, Educational Content
  - TAN HAO SHUAN: Dashboard, Inbox, Personal Stats
- **Screenshots:** Major pages from all three roles

#### e) Conclusions
- Review of work completed
- Validation of objectives achieved
- Discussion of limitations
- Proposed improvements and future enhancements

#### f) References
- APA style citations
- Focus on: websites, sustainability resources, web development documentation
- Future internet use considerations

---

### 4. Web Application (Due: 18 January 2026)

**Technical Requirements:**
- ✅ All files in PHP format
- ✅ HTML5 semantic markup
- ✅ Responsive to different devices (mobile, tablet, desktop)
- ✅ Style sheets (embedded, inline, or external CSS)
- ✅ JavaScript for interactivity and functionality
- ✅ MySQL database aligned with ERD and data dictionary
- ✅ Three user roles clearly demonstrated
- ✅ Usability and accessibility for target users

**Must Include:**
- Appropriate graphics (some team-created)
- Smart bin camera integration simulation
- AI-powered material recognition (Gemini API 2.5)
- Gamification elements (points, badges, streaks)
- Team competition features
- Educational content system
- Analytics dashboard
- Moderation queue


---

### 5. Presentation (Starting: 19 January 2026)

**Format:** 30-minute online demonstration via Microsoft Teams

**Requirements:**
- ✅ All group members must present
- ✅ Each member explains their contribution
- ✅ Live demonstration of features
- ✅ Q&A session

**Presentation Structure:**
1. **Introduction (2 min)** - ALL
   - Project overview
   - Problem statement
   - Solution approach

2. **System Architecture (3 min)** - HENG EE SERN
   - Technology stack
   - Database design
   - System flow

3. **Recycler Features Demo (8 min)**
   - Authentication & Profile (2 min) - HENG EE SERN
   - Dashboard & Inbox (2 min) - TAN HAO SHUAN
   - Team Management (2 min) - MUHAMMAD FARRIS
   - Leaderboard & Challenges (2 min) - LOW ZE XUAN

4. **Eco-Moderator Features Demo (5 min)**
   - Dashboard Overview (1 min) - LAEU ZI-LI
   - AI Review Queue (2 min) - LOW ZE XUAN
   - Educational Content (2 min) - MUHAMMAD FARRIS

5. **Administrator Features Demo (5 min)**
   - Analytics Dashboard (2 min) - HENG EE SERN
   - Challenge Management (2 min) - LOW ZE XUAN
   - Moderator Management (1 min) - MUHAMMAD FARRIS

6. **Unique Features Highlight (4 min)**
   - Smart Bin Integration - LOW ZE XUAN
   - AI Material Recognition - LOW ZE XUAN
   - Gamification Engine - TAN HAO SHUAN
   - Impact Dashboard - LAEU ZI-LI

7. **Conclusions & Q&A (3 min)** - ALL
   - Achievements
   - Challenges faced
   - Future improvements
   - Questions from examiners

**Assessment Criteria (15 marks):**
- Professional presentation (0-3 marks)
- Clear delivery (0-3 marks)
- Teamwork evident (0-3 marks)
- Smooth demo (0-3 marks)
- Confident Q&A (0-3 marks)

---

## ⚠️ Project Restrictions & Academic Integrity

### ❌ STRICTLY PROHIBITED

**Web Creation Tools:**
- Wix, WordPress, Weebly, Squarespace (or any similar WYSIWYG tools)
- **Penalty:** Project rejection

**Pre-built Templates/Frameworks:**
- Bootstrap, Foundation, TemplateMonster, ThemeForest
- **Penalty:** Project rejection

**Code Plagiarism:**
- Copying from other students
- Copying from online repositories without acknowledgment
- Using code from external sources without understanding
- **Penalty:** Academic misconduct procedures

### ⚠️ ALLOWED WITH RESTRICTIONS

**AI-Assisted Tools (ChatGPT, GitHub Copilot, etc.):**
- ✅ Allowed for: Idea generation, debugging guidance
- ❌ Not allowed for: Complete code generation without understanding
- **Requirements:**
  - Must understand and explain all code during presentation
  - Must acknowledge AI assistance in report under "References / Tools Used"
  - Must be able to modify and debug the code independently

**Example Acknowledgment:**
```
Tools Used:
- ChatGPT (OpenAI) - Used for debugging PHP session management issues 
  and generating initial SQL query structure for leaderboard feature.
- GitHub Copilot - Used for JavaScript form validation suggestions.

All code was reviewed, understood, modified, and tested by the team.
```

### ✅ REQUIRED PRACTICES

**Original Work:**
- All code must be written and understood by team
- All designs must be original team creations
- Team-created graphics required

**Proper Attribution:**
- Cite all external resources used
- Document all third-party libraries
- Credit all code snippets from documentation

**Code Demonstration:**
- Each member must explain their code during presentation
- Must answer questions about implementation decisions
- Must demonstrate understanding of all technologies used

---

## 🚀 Getting Started

### Prerequisites
```
- XAMPP/WAMP/LAMP (PHP 8.3.14+ recommended)
- MySQL 9.1.0+ / MariaDB
- Modern web browser (Chrome, Firefox, Edge)
- Gemini API Key (obtain from Google AI Studio)
- Text editor (VS Code, Sublime, PHPStorm)
```

### Installation Steps

1. **Clone/Download Project**
```bash
git clone https://github.com/your-repo/aprecycle.git
cd aprecycle
```

2. **Setup Database**
```bash
# Start XAMPP/WAMP services
# Open phpMyAdmin (http://localhost/phpmyadmin)
# Create new database 'aprecycle'
# Import aprecycle.sql file
```

3. **Configure Database Connection**
```php
// Edit includes/config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'aprecycle');
```

4. **Setup Gemini API**
```php
// Edit api/gemini_integration.php
define('GEMINI_API_KEY', 'your-api-key-here');
```

5. **Start Development Server**
```bash
# Access via browser
http://localhost/aprecycle
```

### Default Test Accounts

**Recycler:**
```
Username: recycler1
Password: password123
```

**Eco-Moderator:**
```
Username: moderator1
Password: password123
```

**Administrator:**
```
Username: admin1
Password: password123
```

---

## 📂 Project Structure
```
aprecycle/
│
├── assets/                          # Static assets
│   ├── css/
│   │   ├── style.css               # Main stylesheet
│   │   ├── responsive.css          # Mobile responsiveness
│   │   ├── recycler.css            # Recycler role styles
│   │   ├── moderator.css           # Moderator role styles
│   │   └── admin.css               # Admin role styles
│   │
│   ├── js/
│   │   ├── main.js                 # Global JavaScript
│   │   ├── validation.js           # Form validation
│   │   ├── charts.js               # Chart.js implementations
│   │   ├── leaderboard.js          # Leaderboard interactions
│   │   └── notifications.js        # Toast notifications
│   │
│   └── images/
│       ├── badges/                 # Badge images
│       ├── materials/              # Material type icons
│       ├── avatars/                # User avatars
│       └── ui/                     # UI elements
│
├── includes/                        # PHP includes
│   ├── config.php                  # Database & API configuration
│   ├── functions.php               # Global functions
│   ├── db_connect.php              # Database connection
│   ├── session.php                 # Session management
│   └── auth_check.php              # Authentication middleware
│
├── recycler/                        # Recycler role pages
│   ├── dashboard.php               # Main dashboard (TAN HAO SHUAN)
│   ├── inbox.php                   # Message list (TAN HAO SHUAN)
│   ├── inbox_details.php           # Message details (TAN HAO SHUAN)
│   ├── profile.php                 # Profile management (HENG EE SERN)
│   ├── team.php                    # Team main menu (MUHAMMAD FARRIS)
│   ├── team_create.php             # Create team (MUHAMMAD FARRIS)
│   ├── team_join.php               # Join team (MUHAMMAD FARRIS)
│   ├── leaderboard.php             # Leaderboard menu (LOW ZE XUAN)
│   ├── leaderboard_individual.php  # Individual rankings (LOW ZE XUAN)
│   ├── leaderboard_team.php        # Team rankings (LOW ZE XUAN)
│   ├── challenges.php              # Challenges list (LOW ZE XUAN)
│   ├── my_challenges.php           # My challenges (LOW ZE XUAN)
│   ├── education.php               # Educational content (MUHAMMAD FARRIS)
│   └── recycle.php                 # Recycling submission
│
├── moderator/                       # Eco-Moderator role pages
│   ├── dashboard.php               # Moderator dashboard (LAEU ZI-LI)
│   ├── review_queue.php            # Review queue (LOW ZE XUAN)
│   ├── review_item.php             # Review single item (LOW ZE XUAN)
│   ├── education_library.php       # Content library (MUHAMMAD FARRIS)
│   ├── education_create.php        # Create content (MUHAMMAD FARRIS)
│   └── education_edit.php          # Edit content (MUHAMMAD FARRIS)
│
├── admin/                           # Administrator role pages
│   ├── dashboard.php               # Admin dashboard (HENG EE SERN)
│   ├── analytics.php               # System analytics (HENG EE SERN)
│   ├── challenges.php              # Challenge management (LOW ZE XUAN)
│   ├── challenge_create.php        # Create challenge (LOW ZE XUAN)
│   ├── challenge_edit.php          # Edit challenge (LOW ZE XUAN)
│   ├── moderators.php              # Moderator list (MUHAMMAD FARRIS)
│   ├── moderator_add.php           # Add moderator (MUHAMMAD FARRIS)
│   ├── moderator_edit.php          # Edit moderator (MUHAMMAD FARRIS)
│   ├── leaderboard.php             # Leaderboard overview (LOW ZE XUAN)
│   └── reports.php                 # Generate reports (LAEU ZI-LI)
│
├── api/                             # API endpoints
│   ├── gemini_integration.php      # Gemini API integration
│   ├── points_calculation.php      # Points calculation logic
│   ├── analytics_data.php          # Analytics data endpoints
│   ├── leaderboard_data.php        # Leaderboard data
│   └── challenge_data.php          # Challenge data
│
├── uploads/                         # User uploads
│   └── submissions/                # Recycling submission images
│
├── docs/                            # Documentation
│   ├── flowcharts/                 # All flowcharts
│   ├── wireframes/                 # All wireframes
│   ├── ERD.pdf                     # Entity Relationship Diagram
│   └── data_dictionary.pdf         # Data Dictionary
│
├── index.php                        # Landing page
├── login.php                        # Login page (HENG EE SERN)
├── register.php                     # Registration page (HENG EE SERN)
├── logout.php                       # Logout handler
├── aprecycle.sql                    # Database dump
└── README.md                        # This file
```

---

## 🎓 Individual Member Responsibilities

### HENG EE SERN (TP081786)

**Assigned Features:**
1. Authentication System (Login/Register/Logout)
2. Profile Management (Recycler)
3. Administrator Dashboard
4. System Analytics Dashboard
5. Point System Configuration

**Deliverables:**
- Authentication flowchart
- Administrator main page flowchart
- ERD design
- Data dictionary
- Administrator dashboard wireframes (desktop & mobile)
- System analytics wireframes (desktop & mobile)
- 2+ code snippets for final report
- Login/Register/Profile PHP files
- Admin dashboard & analytics implementation

**Database Tables Involved:**
- USER (primary)
- RECYCLING_SUBMISSION
- CHALLENGE
- SUBMISSION_MATERIAL

---

### LAEU ZI-LI (TP083604)

**Assigned Features:**
1. Eco-Moderator Dashboard
2. Environmental Impact Metrics & Data (Recycler)
3. Educational Feedback System (Moderator)
4. Report Exportation (Administrator)

**Deliverables:**
- Eco-moderator main page flowchart
- Eco-moderator dashboard wireframes (desktop & mobile)
- 2+ code snippets for final report
- Moderator dashboard PHP file
- Impact metrics display implementation
- Report generation functionality (PDF/CSV)

**Database Tables Involved:**
- RECYCLING_SUBMISSION
- USER
- EDUCATIONAL_CONTENT
- SUBMISSION_MATERIAL
- MATERIAL

---

### LOW ZE XUAN (TP082620)

**Assigned Features:**
1. Recycling Process (Core system)
2. Leaderboard & Rankings (All types)
3. Review Uncertain AI Classifications (Moderator)
4. Challenge Creation & Management (Administrator)
5. Challenge Participation (Recycler)
6. My Challenge (Recycler)

**Deliverables:**
- Recycling process flowchart
- Leaderboard and rankings flowchart
- Review uncertain AI classifications flowchart
- Challenge creation & management flowchart
- Leaderboard wireframes - main menu, individual, team (desktop & mobile)
- Challenge wireframes - main menu, my challenges (desktop & mobile)
- Moderator review wireframes (desktop & mobile)
- Administrator challenge wireframes (desktop & mobile)
- Administrator leaderboard wireframes (desktop & mobile)
- 2+ code snippets for final report
- Complete leaderboard system implementation
- AI review queue system
- Challenge CRUD operations
- Gemini API integration for material recognition

**Database Tables Involved:**
- RECYCLING_SUBMISSION (primary)
- SUBMISSION_MATERIAL
- USER
- CHALLENGE
- USER_CHALLENGE
- TEAM
- MATERIAL
- REWARD
- BADGE

---

### MUHAMMAD FARRIS BIN RAZMAN (TP082730)

**Assigned Features:**
1. Challenge Participation (Recycler)
2. Team Creation & Joining (Recycler)
3. Educational Content Display (Recycler)
4. Create Educational Content (Moderator)
5. Educational Content Library (Moderator)
6. Eco-Moderator Management (Administrator)

**Deliverables:**
- Challenge participation flowchart
- Team creation and joining flowchart
- Create educational content flowchart
- Eco-moderator management flowchart
- Team wireframes - main menu, create, join (desktop & mobile)
- Educational content wireframes for recycler (desktop & mobile)
- Educational content library wireframes for moderator (desktop & mobile)
- Create content wireframes for moderator (desktop & mobile)
- Eco-moderator management wireframes (desktop & mobile)
- Add/update eco-moderator wireframes (desktop & mobile)
- 2+ code snippets for final report
- Complete team management system
- Educational content CRUD system
- Moderator management system

**Database Tables Involved:**
- TEAM (primary)
- USER
- CHALLENGE
- USER_CHALLENGE
- EDUCATIONAL_CONTENT

---

### TAN HAO SHUAN (TP080852)

**Assigned Features:**
1. Recycler Main Page Navigation
2. Personal Dashboard
3. Inbox System (Message List & Details)
4. Team Creation & Joining (Recycler - secondary support)

**Deliverables:**
- Recycler main page flowchart
- Personal dashboard flowchart
- Inbox flowchart
- Recycler dashboard wireframes (desktop & mobile)
- Inbox message list wireframes (desktop & mobile)
- Inbox details wireframes (desktop & mobile)
- 2+ code snippets for final report
- Dashboard implementation with statistics
- Inbox system implementation
- Recycler navigation system

**Database Tables Involved:**
- USER (primary)
- RECYCLING_SUBMISSION
- USER_BADGE
- EDUCATIONAL_CONTENT (for notifications)

---

## 🔗 Integration Points Between Members

### Authentication → All Features
**Integration Owner:** HENG EE SERN  
**Consumers:** ALL MEMBERS  
**Required:** Session management, role-based access control

### Dashboard Statistics → Multiple Sources
**Integration Owner:** TAN HAO SHUAN  
**Data Providers:**
- LOW ZE XUAN (recycling submissions)
- HENG EE SERN (point calculations)
- MUHAMMAD FARRIS (team stats)

### AI Classification → Moderation Queue
**Integration Owner:** LOW ZE XUAN  
**Consumer:** LOW ZE XUAN (moderator review)  
**Data Flow:** RECYCLING_SUBMISSION table with ai_confidence < 80%

### Points System → Multiple Features
**Integration Owner:** HENG EE SERN  
**Consumers:**
- TAN HAO SHUAN (dashboard display)
- LOW ZE XUAN (leaderboard calculations)
- MUHAMMAD FARRIS (challenge points)

### Educational Content → Multiple Roles
**Integration Owner:** MUHAMMAD FARRIS  
**Consumers:**
- Recyclers (view content)
- Moderators (create/edit content)
- TAN HAO SHUAN (inbox notifications)

---

## 📊 Testing Responsibilities

### Unit Testing
- Each member tests their own features independently
- Minimum 5 test cases per major feature

### Integration Testing
**Test Coordinator:** HENG EE SERN  
**Test Scenarios:**
1. Complete recycling workflow (submission → review → points)
2. Challenge participation flow
3. Team competition scenario
4. Educational content publication workflow

### User Acceptance Testing
**Test Coordinator:** LOW ZE XUAN  
**User Roles to Test:**
- Recycler journey (5 scenarios)
- Moderator workflow (3 scenarios)
- Administrator tasks (4 scenarios)

### Responsive Testing
**Responsibility:** ALL MEMBERS (own features)  
**Devices:**
- Desktop (1920×1080)
- Tablet (768×1024)
- Mobile (375×667)

---

#### Documentation
- [ ] Final report in Word/PDF format
- [ ] All flowcharts included
- [ ] ERD finalized
- [ ] Data dictionary complete
- [ ] All wireframes (72 total)
- [ ] Navigation map
- [ ] References in APA format
- [ ] Code snippets (2+ per member = 10+ total)
- [ ] Screenshots of major pages

#### Web Application
- [ ] All PHP files working
- [ ] HTML5 semantic markup used
- [ ] Responsive design tested
- [ ] CSS organized and clean
- [ ] JavaScript functional
- [ ] MySQL database aligned with ERD
- [ ] Three user roles implemented
- [ ] aprecycle.sql file included
- [ ] README.md complete

#### Compressed ZIP File
- [ ] Source code folder
- [ ] Database SQL file
- [ ] Assets folder (images, css, js)
- [ ] Documentation
- [ ] README.md

#### Presentation
- [ ] 30-minute demo prepared
- [ ] All members have sections
- [ ] Backup demo recorded (recommended)
- [ ] Q&A answers prepared

---

**Project Status:** ✅ Preliminary Draft Complete | 🔄 Implementation In Progress  
**Last Updated:** November 27, 2025  
**Next Milestone:** Final Submission - January 18, 2026

---

*For questions, concerns, or issues, please contact any team member via APU email or WhatsApp group.*

**Team APRecycle - Building a Sustainable Future, One Recycle at a Time! ♻️**