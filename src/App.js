import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './index.css';

// Import components
import SplashScreen from './components/SplashScreen';
import OnboardingScreen from './components/OnboardingScreen';
import LoginScreen from './components/LoginScreen';
import HomeScreen from './components/HomeScreen';
import CategoriesScreen from './components/CategoriesScreen';
import ActivityDetailScreen from './components/ActivityDetailScreen';
import BookingScreen from './components/BookingScreen';
import PaymentScreen from './components/PaymentScreen';
import ProfileScreen from './components/ProfileScreen';
import SearchScreen from './components/SearchScreen';

function App() {
  return (
    <Router>
      <div className="App min-h-screen bg-gray-50">
        <Routes>
          <Route path="/" element={<SplashScreen />} />
          <Route path="/onboarding" element={<OnboardingScreen />} />
          <Route path="/login" element={<LoginScreen />} />
          <Route path="/home" element={<HomeScreen />} />
          <Route path="/categories" element={<CategoriesScreen />} />
          <Route path="/search" element={<SearchScreen />} />
          <Route path="/activity/:id" element={<ActivityDetailScreen />} />
          <Route path="/booking/:id" element={<BookingScreen />} />
          <Route path="/payment" element={<PaymentScreen />} />
          <Route path="/profile" element={<ProfileScreen />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;