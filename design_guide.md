# APRecycle Website Design Guide

## 🎨 Visual Identity & Design System

---

## Color Palette Recommendation

### Primary Colors (Eco-Friendly Theme)

**Main Green (Primary Brand Color)**
```
Primary Green: #2D5D3F
- Use for: Headers, primary buttons, navigation bar
- Psychology: Trust, growth, sustainability
- RGB: (45, 93, 63)
- This is a deep, professional forest green
```

**Accent Green (Secondary)**
```
Accent Green: #48BB78
- Use for: Success messages, approved status, hover states
- Psychology: Positive action, verification
- RGB: (72, 187, 120)
- Brighter, more energetic green
```

**Light Green (Background)**
```
Light Green: #C6F6D5
- Use for: Badges, highlight boxes, completed streak days
- Psychology: Calm, achievement
- RGB: (198, 246, 213)
- Soft, pastel background color
```

### Supporting Colors

**Earth Brown (Grounding)**
```
Earth Brown: #8B4513
- Use for: Icons, decorative elements
- Psychology: Natural, organic, recycling
- RGB: (139, 69, 19)
```

**Sky Blue (Trust)**
```
Sky Blue: #4299E1
- Use for: Information boxes, links, educational content
- Psychology: Clean, clear, informative
- RGB: (66, 153, 225)
```

**Sunshine Yellow (Energy)**
```
Sunshine Yellow: #FFD93D
- Use for: Badges, point indicators, rewards
- Psychology: Achievement, optimism
- RGB: (255, 217, 61)
```

### Neutral Colors (Foundation)

```css
/* Whites & Grays */
Pure White: #FFFFFF        /* Backgrounds, cards */
Off-White: #F7FAFC         /* Page background */
Light Gray: #E2E8F0        /* Borders, dividers */
Medium Gray: #718096       /* Secondary text */
Dark Gray: #2D3748         /* Primary text */
Charcoal: #1A202C          /* Headers, emphasis */
```

### Status Colors

```css
Success Green: #48BB78     /* Approved submissions */
Warning Orange: #ED8936    /* Pending reviews */
Error Red: #FC8181         /* Rejected, errors */
Info Blue: #4299E1         /* Informational messages */
```

---

## Complete CSS Color System

```css
/**
 * APRecycle Design System - Color Variables
 * Use these CSS variables throughout your project
 */

:root {
    /* Primary Brand Colors */
    --color-primary: #2D5D3F;
    --color-primary-light: #3A7A54;
    --color-primary-dark: #1F4129;
    --color-secondary: #48BB78;
    --color-secondary-light: #68D391;
    
    /* Accent Colors */
    --color-accent-blue: #4299E1;
    --color-accent-yellow: #FFD93D;
    --color-accent-brown: #8B4513;
    
    /* Neutral Palette */
    --color-white: #FFFFFF;
    --color-gray-50: #F7FAFC;
    --color-gray-100: #EDF2F7;
    --color-gray-200: #E2E8F0;
    --color-gray-300: #CBD5E0;
    --color-gray-400: #A0AEC0;
    --color-gray-500: #718096;
    --color-gray-600: #4A5568;
    --color-gray-700: #2D3748;
    --color-gray-800: #1A202C;
    --color-gray-900: #171923;
    
    /* Status Colors */
    --color-success: #48BB78;
    --color-success-light: #C6F6D5;
    --color-warning: #ED8936;
    --color-warning-light: #FEEBC8;
    --color-error: #FC8181;
    --color-error-light: #FED7D7;
    --color-info: #4299E1;
    --color-info-light: #BEE3F8;
    
    /* Gradient Backgrounds */
    --gradient-primary: linear-gradient(135deg, #2D5D3F 0%, #48BB78 100%);
    --gradient-success: linear-gradient(135deg, #48BB78 0%, #38A169 100%);
    --gradient-badge: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    
    /* Shadows */
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    --shadow-md: 0 2px 8px rgba(0,0,0,0.1);
    --shadow-lg: 0 4px 12px rgba(0,0,0,0.15);
    --shadow-xl: 0 10px 40px rgba(0,0,0,0.2);
    
    /* Border Radius */
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;
    --radius-full: 9999px;
    
    /* Spacing Scale */
    --space-1: 0.25rem;   /* 4px */
    --space-2: 0.5rem;    /* 8px */
    --space-3: 0.75rem;   /* 12px */
    --space-4: 1rem;      /* 16px */
    --space-5: 1.25rem;   /* 20px */
    --space-6: 1.5rem;    /* 24px */
    --space-8: 2rem;      /* 32px */
    --space-10: 2.5rem;   /* 40px */
    --space-12: 3rem;     /* 48px */
    
    /* Typography */
    --font-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 
                 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 
                 'Helvetica Neue', sans-serif;
    --font-mono: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    
    /* Font Sizes */
    --text-xs: 0.75rem;    /* 12px */
    --text-sm: 0.875rem;   /* 14px */
    --text-base: 1rem;     /* 16px */
    --text-lg: 1.125rem;   /* 18px */
    --text-xl: 1.25rem;    /* 20px */
    --text-2xl: 1.5rem;    /* 24px */
    --text-3xl: 1.875rem;  /* 30px */
    --text-4xl: 2.25rem;   /* 36px */
}
```

---

## Typography System

### Font Recommendations

**Option 1: System Fonts (Fast, Always Available)**
```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 
             'Oxygen', 'Ubuntu', 'Cantarell', 'Helvetica Neue', sans-serif;
```
✅ Best choice - fast loading, professional

**Option 2: Google Fonts (If allowed)**
```html
<!-- In HTML head -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```
```css
font-family: 'Inter', sans-serif;
```

### Font Scale
```css
/* Headers */
h1 { font-size: 2.25rem; font-weight: 700; } /* 36px */
h2 { font-size: 1.875rem; font-weight: 600; } /* 30px */
h3 { font-size: 1.5rem; font-weight: 600; }   /* 24px */
h4 { font-size: 1.25rem; font-weight: 600; }  /* 20px */

/* Body Text */
body { font-size: 1rem; line-height: 1.6; }   /* 16px */
small { font-size: 0.875rem; }                 /* 14px */
```

---

## Component Design Patterns

### 1. Buttons

```css
/* Primary Button - Main Actions */
.btn-primary {
    background: var(--color-primary);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
    background: var(--color-primary-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Success Button - Approve Actions */
.btn-success {
    background: var(--color-success);
    color: white;
}

/* Danger Button - Reject/Delete */
.btn-danger {
    background: var(--color-error);
    color: white;
}

/* Secondary Button - Cancel/Back */
.btn-secondary {
    background: var(--color-gray-200);
    color: var(--color-gray-700);
}
```

### 2. Cards

```css
.card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--space-6);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

/* Stat Card - Dashboard Statistics */
.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    background: white;
    padding: var(--space-5);
    border-radius: var(--radius-lg);
    border-left: 4px solid var(--color-primary);
}
```

### 3. Navigation Bar

```css
.navbar {
    background: var(--color-primary);
    color: white;
    padding: var(--space-4) var(--space-6);
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-items a {
    color: white;
    text-decoration: none;
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-md);
    transition: background 0.3s ease;
}

.nav-items a:hover,
.nav-items a.active {
    background: rgba(255, 255, 255, 0.1);
}
```

### 4. Status Badges

```css
.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 600;
}

.badge-success {
    background: var(--color-success-light);
    color: var(--color-success);
}

.badge-warning {
    background: var(--color-warning-light);
    color: var(--color-warning);
}

.badge-error {
    background: var(--color-error-light);
    color: var(--color-error);
}
```

---

## Design Principles for APRecycle

### 1. **Green & Clean** 🌿
- Lots of white space
- Green accents, not overwhelming
- Nature-inspired icons (leaves, earth, recycling symbols)

### 2. **Friendly & Approachable** 😊
- Rounded corners (not sharp edges)
- Smooth animations
- Encouraging messages
- Emoji use where appropriate

### 3. **Clear Hierarchy** 📊
- Most important actions are green (primary)
- Statistics use large numbers with small labels
- White cards on light gray backgrounds

### 4. **Gamification Visual Cues** 🎮
- Points: Gold/Yellow
- Streaks: Fire emoji + green
- Badges: Colorful gradients
- Achievements: Celebratory colors

### 5. **Status Communication** ✓
- Green ✓ = Approved/Success
- Orange ⏳ = Pending
- Red ✗ = Rejected/Error
- Blue ℹ = Information

---

## Layout Recommendations

### Dashboard Layout
```
┌─────────────────────────────────────┐
│  Navigation Bar (Green)              │
├─────────────────────────────────────┤
│                                      │
│  User Profile Card (White)           │
│  ┌───┐ Name & Level                 │
│  │ A │                               │
│  └───┘                               │
│                                      │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐│
│  │Points│ │  CO₂ │ │Streak│ │Badge││
│  │ 1250 │ │ 45kg │ │7 days│ │  12 ││
│  └──────┘ └──────┘ └──────┘ └──────┘│
│                                      │
│  Streak Tracker (7-day visual)       │
│  ☑️ ☑️ ☑️ ☑️ ☑️ ☑️ ☑️                   │
│                                      │
│  Quick Actions Grid                  │
│  ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐   │
│  │♻️   │ │📚   │ │🏆   │ │🎖️  │   │
│  └─────┘ └─────┘ └─────┘ └─────┘   │
│                                      │
│  Recent Activity List                │
│  ✓ Recycled 2 Plastic Bottles        │
│  ⏳ Recycled 3 Aluminum Cans          │
│                                      │
└─────────────────────────────────────┘
```

### Responsive Breakpoints
```css
/* Mobile First */
/* Default: 320px - 767px (single column) */

/* Tablet */
@media (min-width: 768px) {
    /* 2 columns for stats, actions */
}

/* Desktop */
@media (min-width: 1024px) {
    /* 4 columns for stats */
    /* 3-4 columns for actions */
}

/* Large Desktop */
@media (min-width: 1280px) {
    /* Max width container: 1200px */
}
```

---

## Icon Recommendations

### Free Icon Options (No Copyright Issues)

**Option 1: Unicode Emoji** ✅ Easiest
```
♻️ Recycle
🌱 Growth
🌍 Earth
🏆 Achievement
⭐ Star
✓ Check
✗ Cross
📊 Stats
💰 Points
🔥 Streak
```

**Option 2: CSS-Only Icons**
Create simple shapes with CSS for a unique look

**Option 3: Font Awesome (Free version)**
```html
<!-- If allowed to use CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
```

---

## Sample Page Mockup (Dashboard)

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - APRecycle</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F7FAFC;
            color: #2D3748;
        }
        
        .navbar {
            background: #2D5D3F;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .user-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            font-weight: bold;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
        }
        
        .stat-icon {
            font-size: 2rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2D5D3F;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #718096;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🌱 APRecycle</h1>
    </nav>
    
    <div class="container">
        <div class="user-card">
            <div class="avatar">T</div>
            <div>
                <h2>Tan Hao Shuan</h2>
                <p style="color: #718096;">Recycler | Level 5</p>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div>
                    <div class="stat-value">1,250</div>
                    <div class="stat-label">Total Points</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🌍</div>
                <div>
                    <div class="stat-value">45kg</div>
                    <div class="stat-label">CO₂ Reduced</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔥</div>
                <div>
                    <div class="stat-value">7 days</div>
                    <div class="stat-label">Current Streak</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div>
                    <div class="stat-value">12</div>
                    <div class="stat-label">Badges Earned</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

---

## Quick Start CSS Template

Save this as `assets/css/main.css`:

```css
/* APRecycle Main Stylesheet */
@import url('variables.css'); /* Your color variables */

/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-sans);
    background: var(--color-gray-50);
    color: var(--color-gray-700);
    line-height: 1.6;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--space-6) var(--space-4);
}

/* Buttons */
.btn {
    padding: var(--space-3) var(--space-6);
    border-radius: var(--radius-md);
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Cards */
.card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--space-6);
}

/* Utilities */
.text-center { text-align: center; }
.text-right { text-align: right; }
.mb-2 { margin-bottom: var(--space-2); }
.mb-4 { margin-bottom: var(--space-4); }
.mb-6 { margin-bottom: var(--space-6); }
.mt-2 { margin-top: var(--space-2); }
.mt-4 { margin-top: var(--space-4); }
```

---

## Design Don'ts ❌

1. **Don't use too many colors** - Stick to the palette
2. **Don't use pure black (#000000)** - Use dark gray instead
3. **Don't make everything green** - Use white as primary, green as accent
4. **Don't forget mobile** - Test on small screens
5. **Don't use tiny fonts** - Minimum 14px for body text
6. **Don't skip hover states** - Users need feedback
7. **Don't overuse animations** - Keep it subtle

---

## Design Dos ✅

1. **Do use consistent spacing** - Follow the spacing scale
2. **Do add shadows to cards** - Creates depth
3. **Do round corners** - More friendly feel
4. **Do use icons** - Makes UI more intuitive
5. **Do keep contrast high** - For accessibility
6. **Do test on real devices** - Not just DevTools
7. **Do celebrate achievements** - Use bright colors for success

---

This design system will give your APRecycle project a professional, cohesive, and eco-friendly look! 🌿✨