# 📱 Dive in Adventure - Prototype Preview

## 🎯 **How to Access the Live Prototype**

The prototype is running on: **`http://localhost:3000`**

## 📱 **Screen Flow Overview**

### 1. **Splash Screen** (`/`)
```
┌─────────────────────────┐
│                         │
│       🏔️ + 🌊           │
│                         │
│   Dive in Adventure     │
│ Discover Amazing        │
│    Adventures          │
│                         │
│        • • •           │
│     Loading...         │
└─────────────────────────┘
```
- Beautiful animated logo
- Gradient background (Blue to Orange)
- Auto-navigates to onboarding in 3 seconds

### 2. **Onboarding** (`/onboarding`)
```
Step 1/4: Discovery
┌─────────────────────────┐
│      [Skip]             │
│                         │
│        📍              │
│                         │
│  Discover Adventures    │
│      Nearby            │
│                         │
│ Find exciting adventure │
│ activities in your area │
│                         │
│    ● ○ ○ ○             │
│        [Next]          │
└─────────────────────────┘
```
- 4 interactive steps
- Features: Discovery, Booking, Payment, Safety
- Smooth animations between steps

### 3. **Login/Register** (`/login`)
```
┌─────────────────────────┐
│     🏔️+🌊  [Logo]       │
│                         │
│    Welcome Back!        │
│ Sign in to discover     │
│  amazing adventures     │
│                         │
│ [📧 Email           ]   │
│ [🔒 Password        ]   │
│ [ ] Remember me         │
│                         │
│     [Sign In]           │
│                         │
│  [Google] [Facebook]    │
│                         │
│ Don't have account?     │
│      Sign up            │
└─────────────────────────┘
```

### 4. **Home Screen** (`/home`)
```
┌─────────────────────────┐
│ 🏔️ Good morning!    🔔👤 │
│    Ready for adventure? │
│ 📍 Dhaka, Bangladesh    │
│ [🔍 Search adventures...│
│                         │
│ Categories              │
│ [🪂Sky] [🤿Sea] [🥾Land]│
│                         │
│ Featured Adventures     │
│ ┌─────────────────────┐ │
│ │🪂 Skydiving Exp.   │ │
│ │Cox's Bazar ⭐4.8   │ │
│ │৳12,000  [20% OFF]  │ │
│ └─────────────────────┘ │
│                         │
│ Near You               │
│ • Bungee Jumping       │
│ • Paragliding          │
└─────────────────────────┘
```

### 5. **Categories** (`/categories`)
```
┌─────────────────────────┐
│ ← Adventure Categories   │
│ [🔍 Search activities...│
│                         │
│ [All][Extreme][Water]   │
│ [Land][Air][Wildlife]   │
│                         │
│ 10 Activities Found     │
│                         │
│ ┌🪂 Skydiving        ⭐│
│ │Experience ultimate  4.8│
│ │adrenaline rush        │
│ │15 locations ৳10-15k   │
│ │              [Extreme]│
│ └─────────────────────────│
│                         │
│ ┌🤿 Scuba Diving     ⭐│
│ │Explore underwater   4.9│
│ │wonders                │
│ │23 locations ৳6-12k    │
│ │            [Moderate] │
│ └─────────────────────────│
└─────────────────────────┘
```

### 6. **Activity Details** (`/activity/1`)
```
┌─────────────────────────┐
│ ← 🔗 ❤️                 │
│                         │
│        🪂               │
│   Professional          │
│    Skydiving           │
│                 [20% OFF]│
│                         │
│ Skydiving Experience    │
│ 📍 Cox's Bazar         │
│ ⭐ 4.8 (156 reviews)   │
│                         │
│ 🕒 3 hours 👥 1-4 people│
│ 🛡️ Extreme              │
│                         │
│ ৳12,000 ৳15,000        │
│ per person              │
│                         │
│[Overview][Itinerary]    │
│[Reviews][Provider]      │
│                         │
│ Starting from ৳12,000   │
│         [📅 Book Now]   │
└─────────────────────────┘
```

### 7. **Booking Process** (`/booking/1`)
```
Step 1: Date & Time
┌─────────────────────────┐
│ ← Book Adventure        │
│ ●──●──○ Date & Time     │
│                         │
│ 🪂 Skydiving Experience │
│ 📍 Cox's Bazar • 3 hours│
│                         │
│ Select Date             │
│ [Mo][Tu][We][Th][Fr]    │
│ [25][26][27][28][29]    │
│                         │
│ Available Times         │
│ [08:00 AM] 3 slots      │
│ [10:00 AM] 2 slots ✓    │
│ [02:00 PM] 4 slots      │
│                         │
│ Guests: [-] 1 [+]       │
│                         │
│ Total: ৳12,600          │
│         [Continue]      │
└─────────────────────────┘
```

### 8. **Payment** (`/payment`)
```
┌─────────────────────────┐
│ ← Payment               │
│                         │
│ Booking Summary         │
│ Activity: Skydiving     │
│ Date: Dec 25 • 10:00 AM │
│ Guests: 1 person        │
│ Total: ৳12,600          │
│                         │
│ Payment Method          │
│ [💳] Credit/Debit Card ✓│
│ [📱] bKash              │
│ [📱] Nagad              │
│ [📱] Rocket             │
│                         │
│ Card Details            │
│ [💳 1234 5678 9012 3456]│
│ [👤 John Doe           ]│
│ [📅 12/25] [🔒 123     ]│
│                         │
│ 🔒 Secure Payment       │
│                         │
│      [Pay ৳12,600]      │
└─────────────────────────┘
```

### 9. **Profile** (`/profile`)
```
┌─────────────────────────┐
│ ← Profile               │
│                         │
│ 👩‍💼 📷                    │
│ Sarah Ahmed  ✏️          │
│ sarah@email.com         │
│ Member since Jan 2024   │
│                         │
│    12        8          │
│ Bookings  Completed     │
│                         │
│[Bookings][Favorites]    │
│         [Settings]      │
│                         │
│ Booking History         │
│ ┌🪂 Skydiving Exp    ✓ │
│ │Cox's Bazar  ⭐5     │
│ │Jan 15 • ৳12,000     │
│ └─────────────────────────│
│ ┌🤿 Scuba Diving     ✓ │
│ │Saint Martin ⭐4     │
│ │Jan 20 • ৳8,500      │
│ └─────────────────────────│
└─────────────────────────┘
```

## 🎨 **Design Features**

### **Colors**
- Primary: Ocean Blue (#0ea5e9 → #0284c7)
- Adventure: Orange (#f97316 → #ea580c)
- Success: Green (#22c55e)
- Background: Light Gray (#f9fafb)

### **Typography**
- Headers: Poppins (600-800 weight)
- Body: Inter (400-600 weight)

### **Animations**
- Page transitions: 300ms ease
- Button hover: Scale 1.02
- Loading states: Smooth spinners
- Card hover: Lift effect

## 🚀 **Navigation Flow**
```
Splash → Onboarding → Login → Home
  ↓
Home ↔ Search ↔ Categories ↔ Activity Details
  ↓
Activity Details → Booking → Payment → Success → Home
  ↓
Home → Profile (Bookings/Favorites/Settings)
```

## 📱 **Key Interactions**
1. **Swipe gestures** for onboarding
2. **Touch-friendly** buttons (44px minimum)
3. **Pull-to-refresh** on lists
4. **Bottom navigation** for main tabs
5. **Modal overlays** for forms
6. **Real-time validation** on inputs

---

**🌐 Access the live prototype at: `http://localhost:3000`**