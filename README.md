# Dive in Adventure - Adventure Booking App Prototype

A professional React-based prototype for an adventure booking mobile application that helps users discover and book thrilling adventure activities near them.

## 🌟 Features

### 🎯 Core Functionality
- **Location-based Discovery**: Find adventure activities near your location
- **Comprehensive Search**: Advanced search with filters for activities, location, price, and difficulty
- **Detailed Activity Pages**: Complete information with photos, reviews, itineraries, and provider details
- **Smart Booking System**: Multi-step booking process with date/time selection and guest management
- **Multiple Payment Options**: Support for bKash, Nagad, Rocket, and international cards
- **User Profiles**: Complete user management with booking history and favorites

### 🏃‍♂️ Adventure Activities
- Skydiving
- Scuba Diving
- Bungee Jumping
- Hiking/Trekking
- Safari Tours
- Paragliding
- White-water Rafting
- Zip Lining
- Rock Climbing
- Cave Exploration

### 📱 User Experience
- **Engaging Onboarding**: Multi-step introduction to app features
- **Intuitive Navigation**: Bottom navigation with smooth transitions
- **Professional Design**: Modern UI with gradient themes and animations
- **Mobile-First**: Responsive design optimized for mobile devices
- **Smooth Animations**: Framer Motion powered transitions

### 💳 Payment Integration
- **bKash**: Mobile financial service
- **Nagad**: Digital financial service  
- **Rocket**: Dutch-Bangla Bank mobile banking
- **Credit/Debit Cards**: Visa, Mastercard, American Express
- **Secure Processing**: Encrypted payment handling

## 🛠 Technology Stack

- **Frontend**: React 18.2.0
- **Routing**: React Router DOM 6.8.0
- **Styling**: Tailwind CSS 3.3.6
- **Icons**: Lucide React 0.344.0
- **Animations**: Framer Motion 10.16.0
- **UI Components**: Headless UI 1.7.17

## 🚀 Getting Started

### Prerequisites
- Node.js (version 14 or higher)
- npm or yarn

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd dive-in-adventure-prototype
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   npm start
   ```

4. **Open your browser**
   Navigate to `http://localhost:3000` to view the app

## 📱 App Flow

### 1. **Splash Screen** (`/`)
- Beautiful animated logo with app branding
- Automatic navigation to onboarding after 3 seconds

### 2. **Onboarding** (`/onboarding`)
- 4-step interactive introduction
- Feature highlights with smooth animations
- Skip option available

### 3. **Authentication** (`/login`)
- Login/Register toggle functionality
- Social login options (Google, Facebook)
- Email and phone number registration

### 4. **Home Screen** (`/home`)
- Location-based adventure discovery
- Search functionality with filters
- Featured adventures carousel
- Category navigation
- Nearby activities with quick booking

### 5. **Categories** (`/categories`)
- Comprehensive activity listing
- Filter by category, price, and difficulty
- Search within categories
- Activity popularity ratings

### 6. **Search** (`/search`)
- Advanced search with filters
- Recent and popular searches
- Real-time results
- Filter by price, difficulty, duration

### 7. **Activity Details** (`/activity/:id`)
- Comprehensive activity information
- Photo gallery and descriptions
- Reviews and ratings
- Provider information
- Booking availability
- Tabbed interface (Overview, Itinerary, Reviews, Provider)

### 8. **Booking Process** (`/booking/:id`)
- 3-step booking process:
  1. Date & Time Selection
  2. Guest Information
  3. Review & Summary
- Real-time availability checking
- Price calculations with discounts
- Guest details collection

### 9. **Payment** (`/payment`)
- Multiple payment method selection
- Secure card input with validation
- Mobile banking integration (bKash, Nagad, Rocket)
- Payment processing simulation
- Success confirmation

### 10. **User Profile** (`/profile`)
- User information management
- Booking history with status tracking
- Favorite activities
- Account settings
- Sign out functionality

## 🎨 Design Features

### Color Scheme
- **Primary**: Ocean blue gradient (#0ea5e9 to #0284c7)
- **Adventure**: Orange gradient (#f97316 to #ea580c)
- **Success**: Green (#22c55e)
- **Background**: Light gray (#f9fafb)

### Typography
- **Display Font**: Poppins (headings)
- **Body Font**: Inter (content)
- **Font Weights**: 300-800 range

### Animations
- Smooth page transitions
- Hover effects on interactive elements
- Loading states and micro-interactions
- Gesture-based animations

## 🔧 Configuration

### Tailwind Configuration
Custom color palette, fonts, and animations are configured in `tailwind.config.js`

### Environment Setup
The app uses standard Create React App configuration with additional PostCSS for Tailwind processing.

## 📊 Prototype Data

The app uses mock data for demonstration purposes:
- Adventure activities with realistic pricing in Bangladeshi Taka (৳)
- User profiles and booking history
- Reviews and ratings
- Location data for Bangladesh (Cox's Bazar, Dhaka, Bandarban, etc.)

## 🌍 Localization

- Primary language: English
- Currency: Bangladeshi Taka (৳)
- Location data: Bangladesh-focused
- Phone number format: Bangladesh (+880)

## 📱 Mobile Optimization

- Touch-friendly interface design
- Gesture navigation support
- Optimized for iOS and Android
- Bottom navigation for easy thumb access
- Swipe gestures for enhanced UX

## 🔒 Security Features

- Input validation and sanitization
- Secure payment form handling
- Password visibility toggles
- Form validation feedback

## 🎯 Use Cases for Figma Design

This prototype serves as a comprehensive reference for creating a professional Figma design:

1. **Complete User Flows**: All major user journeys mapped out
2. **Interaction Patterns**: Button states, form interactions, navigation
3. **Component Library**: Reusable cards, buttons, forms, and layouts
4. **Color and Typography**: Consistent design system
5. **Mobile-First Design**: Responsive layouts and touch interactions
6. **Animation Guidelines**: Transition timing and effects
7. **Content Structure**: Real-world content examples and data modeling

## 🚀 Deployment

For production deployment:

1. **Build the app**
   ```bash
   npm run build
   ```

2. **Deploy to hosting service**
   - Vercel, Netlify, or AWS S3
   - Configure routing for single-page application

## 🤝 Contributing

This is a prototype for design reference. For real-world implementation:
- Add backend API integration
- Implement real payment gateways
- Add user authentication
- Include location services
- Add push notifications

## 📄 License

This project is created for prototype and design reference purposes.

---

**Note**: This is a complete prototype demonstrating the user experience and design patterns for the Dive in Adventure app. All payment processing, user authentication, and booking confirmations are simulated for demonstration purposes.