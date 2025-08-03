import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  MapPin, 
  Calendar, 
  CreditCard, 
  Shield, 
  ChevronLeft, 
  ChevronRight,
  Plane,
  Waves,
  Mountain
} from 'lucide-react';

const onboardingData = [
  {
    id: 1,
    icon: MapPin,
    title: "Discover Adventures Nearby",
    description: "Find exciting adventure activities in your area with real-time location tracking and personalized recommendations.",
    color: "text-primary-500",
    bgColor: "bg-primary-100"
  },
  {
    id: 2,
    icon: Calendar,
    title: "Easy Booking System",
    description: "Book your adventure instantly with our simple booking system. Choose your date, time, and group size effortlessly.",
    color: "text-adventure-500",
    bgColor: "bg-adventure-100"
  },
  {
    id: 3,
    icon: CreditCard,
    title: "Secure Payment Options",
    description: "Pay safely with multiple options including bKash, Nagad, and international cards. Your payment is always protected.",
    color: "text-success-500",
    bgColor: "bg-success-100"
  },
  {
    id: 4,
    icon: Shield,
    title: "Safety First",
    description: "All our adventure partners are verified and certified. Enjoy your adventure with complete peace of mind.",
    color: "text-purple-500",
    bgColor: "bg-purple-100"
  }
];

const OnboardingScreen = () => {
  const [currentStep, setCurrentStep] = useState(0);
  const navigate = useNavigate();

  const nextStep = () => {
    if (currentStep < onboardingData.length - 1) {
      setCurrentStep(currentStep + 1);
    } else {
      navigate('/login');
    }
  };

  const prevStep = () => {
    if (currentStep > 0) {
      setCurrentStep(currentStep - 1);
    }
  };

  const skipOnboarding = () => {
    navigate('/login');
  };

  const currentData = onboardingData[currentStep];
  const IconComponent = currentData.icon;

  return (
    <div className="min-h-screen bg-white relative overflow-hidden">
      {/* Background decorations */}
      <div className="absolute inset-0 overflow-hidden">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-primary-100 rounded-full opacity-50"></div>
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-adventure-100 rounded-full opacity-50"></div>
        <Plane className="absolute top-20 right-20 w-8 h-8 text-primary-300 transform rotate-45" />
        <Mountain className="absolute bottom-40 right-40 w-12 h-12 text-adventure-300" />
        <Waves className="absolute bottom-20 left-20 w-10 h-10 text-primary-300" />
      </div>

      {/* Skip button */}
      <div className="absolute top-12 right-6 z-10">
        <button
          onClick={skipOnboarding}
          className="text-gray-500 hover:text-gray-700 font-medium"
        >
          Skip
        </button>
      </div>

      {/* Main content */}
      <div className="flex flex-col items-center justify-center min-h-screen px-6 relative z-10">
        <AnimatePresence mode="wait">
          <motion.div
            key={currentStep}
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -50 }}
            transition={{ duration: 0.3 }}
            className="text-center max-w-sm"
          >
            {/* Icon */}
            <div className={`w-32 h-32 mx-auto mb-8 ${currentData.bgColor} rounded-3xl flex items-center justify-center shadow-lg`}>
              <IconComponent className={`w-16 h-16 ${currentData.color}`} />
            </div>

            {/* Title */}
            <h2 className="text-3xl font-display font-bold text-gray-900 mb-4">
              {currentData.title}
            </h2>

            {/* Description */}
            <p className="text-lg text-gray-600 leading-relaxed mb-8">
              {currentData.description}
            </p>
          </motion.div>
        </AnimatePresence>

        {/* Progress indicators */}
        <div className="flex space-x-3 mb-12">
          {onboardingData.map((_, index) => (
            <div
              key={index}
              className={`w-3 h-3 rounded-full transition-all duration-300 ${
                index === currentStep 
                  ? 'bg-primary-500 w-8' 
                  : index < currentStep 
                    ? 'bg-primary-300' 
                    : 'bg-gray-200'
              }`}
            />
          ))}
        </div>

        {/* Navigation buttons */}
        <div className="flex items-center justify-between w-full max-w-sm">
          <button
            onClick={prevStep}
            disabled={currentStep === 0}
            className={`p-3 rounded-xl transition-all duration-200 ${
              currentStep === 0
                ? 'text-gray-300 cursor-not-allowed'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
            }`}
          >
            <ChevronLeft className="w-6 h-6" />
          </button>

          <button
            onClick={nextStep}
            className="btn-primary flex items-center space-x-2 min-w-[120px] justify-center"
          >
            <span>
              {currentStep === onboardingData.length - 1 ? 'Get Started' : 'Next'}
            </span>
            <ChevronRight className="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  );
};

export default OnboardingScreen;