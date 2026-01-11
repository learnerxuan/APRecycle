# APRecycle System - Test Credentials

## Login Credentials

All users share the same password: **`password123`**

---

## Administrator Accounts

### Admin 1
- **Username:** `admin1`
- **Password:** `password123`
- **Email:** admin@aprecycle.com
- **Role:** Administrator
- **Access:** Full system management, analytics, CRUD operations

### Admin 2
- **Username:** `admin2`
- **Password:** `password123`
- **Email:** admin2@aprecycle.com
- **Role:** Administrator
- **Access:** Full system management, analytics, CRUD operations

---

## Eco-Moderator Accounts

### Moderator 1
- **Username:** `moderator1`
- **Password:** `password123`
- **Email:** moderator1@aprecycle.com
- **Role:** Eco-Moderator
- **Access:** Review queue, approve/reject submissions, educational content management

### Moderator 2
- **Username:** `moderator2`
- **Password:** `password123`
- **Email:** moderator2@aprecycle.com
- **Role:** Eco-Moderator
- **Access:** Review queue, approve/reject submissions, educational content management

### Moderator 3
- **Username:** `moderator3`
- **Password:** `password123`
- **Email:** moderator3@aprecycle.com
- **Role:** Eco-Moderator
- **Access:** Review queue, approve/reject submissions, educational content management

---

## Recycler Accounts (Sample Users)

### Active Recyclers with Teams

**john_warrior** (Green Warriors Team)
- **Username:** `john_warrior`
- **Password:** `password123`
- **Email:** john@student.apu.edu.my
- **Points:** 450
- **Team:** Green Warriors

**sarah_green** (Green Warriors Team)
- **Username:** `sarah_green`
- **Password:** `password123`
- **Email:** sarah@student.apu.edu.my
- **Points:** 420
- **Team:** Green Warriors

**emma_legend** (Eco Legends Team)
- **Username:** `emma_legend`
- **Password:** `password123`
- **Email:** emma@student.apu.edu.my
- **Points:** 390
- **Team:** Eco Legends

### Solo Recyclers (No Team)

**kevin_solo**
- **Username:** `kevin_solo`
- **Password:** `password123`
- **Email:** kevin@student.apu.edu.my
- **Points:** 250
- **Team:** None

---

## Quick Reference

| Role | Sample Username | Password | Access Level |
|------|----------------|----------|--------------|
| **Administrator** | admin1 | password123 | Full system control |
| **Eco-Moderator** | moderator1 | password123 | Review & content management |
| **Recycler** | john_warrior | password123 | Recycling, challenges, teams |

---

## Notes

- All passwords are hashed in the database using bcrypt (`$2y$10$...`)
- The plaintext password `password123` is used for all test accounts
- For production, users should set strong, unique passwords
- QR codes are pre-generated for all recycler accounts
