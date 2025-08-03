import React, { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, 
  Calendar,
  Clock,
  Users,
  User,
  Mail,
  Phone,
  MapPin,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Plus,
  Minus,
  AlertCircle,
  CheckCircle
} from 'lucide-react';

const BookingScreen = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  
  const [selectedDate, setSelectedDate] = useState(null);
  const [selectedTime, setSelectedTime] = useState(null);
  const [guests, setGuests] = useState(1);
  const [guestDetails, setGuestDetails] = useState({
    name: '',
    email: '',
    phone: '',
    emergencyContact: '',
    specialRequests: ''
  });
  const [currentStep, setCurrentStep] = useState(1);

  // Mock activity data
  const activity = {
    id: 1,
    title: "Skydiving Experience",
    location: "Cox's Bazar, Bangladesh",
    price: 12000,
    originalPrice: 15000,
    discount: 20,
    duration: "3 hours",
    maxGuests: 4,
    image: "🪂"
  };

  // Generate available dates (next 30 days)
  const generateAvailableDates = () => {
    const dates = [];
    const today = new Date();
    
    for (let i = 1; i <= 30; i++) {
      const date = new Date(today);
      date.setDate(today.getDate() + i);
      dates.push(date);
    }
    
    return dates;
  };

  const availableDates = generateAvailableDates();
  const availableTimes = [
    { time: '08:00 AM', available: true, slots: 3 },
    { time: '10:00 AM', available: true, slots: 2 },
    { time: '12:00 PM', available: false, slots: 0 },
    { time: '02:00 PM', available: true, slots: 4 },
    { time: '04:00 PM', available: true, slots: 1 }
  ];

  const formatDate = (date) => {
    return date.toLocaleDateString('en-US', { 
      weekday: 'short', 
      month: 'short', 
      day: 'numeric' 
    });
  };

  const isDateSelected = (date) => {
    return selectedDate && 
           date.toDateString() === selectedDate.toDateString();
  };

  const handleGuestChange = (change) => {
    const newGuests = Math.max(1, Math.min(activity.maxGuests, guests + change));
    setGuests(newGuests);
  };

  const handleInputChange = (field, value) => {
    setGuestDetails(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const calculateTotal = () => {
    const subtotal = activity.price * guests;
    const discount = activity.discount ? (subtotal * activity.discount / 100) : 0;
    const serviceFee = subtotal * 0.05; // 5% service fee
    const total = subtotal - discount + serviceFee;
    
    return {
      subtotal,
      discount,
      serviceFee,
      total
    };
  };

  const pricing = calculateTotal();

  const canProceed = () => {
    switch (currentStep) {
      case 1:
        return selectedDate && selectedTime;
      case 2:
        return guestDetails.name && guestDetails.email && guestDetails.phone;
      case 3:
        return true;
      default:
        return false;
    }
  };

  const nextStep = () => {
    if (canProceed()) {
      if (currentStep < 3) {
        setCurrentStep(currentStep + 1);
      } else {
        // Navigate to payment with booking details
        navigate('/payment', { 
          state: { 
            activity, 
            selectedDate, 
            selectedTime, 
            guests, 
            guestDetails, 
            pricing 
          } 
        });
      }
    }
  };

  const prevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    } else {
      navigate(-1);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white shadow-sm border-b border-gray-100"
      >
        <div className="px-6 py-4">
          <div className="flex items-center space-x-3 mb-4">
            <button 
              onClick={prevStep}
              className="p-2 text-gray-400 hover:text-gray-600 -ml-2"
            >
              <ArrowLeft className="w-6 h-6" />
            </button>
            <h1 className="text-xl font-display font-semibold text-gray-900">Book Adventure</h1>
          </div>

          {/* Progress indicator */}
          <div className="flex items-center space-x-4">
            {[1, 2, 3].map((step) => (
              <div key={step} className="flex items-center">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${
                  step <= currentStep 
                    ? 'bg-primary-600 text-white' 
                    : 'bg-gray-200 text-gray-500'
                }`}>
                  {step < currentStep ? (
                    <CheckCircle className="w-5 h-5" />
                  ) : (
                    step
                  )}
                </div>
                {step < 3 && (
                  <div className={`w-12 h-1 mx-2 ${
                    step < currentStep ? 'bg-primary-600' : 'bg-gray-200'
                  }`} />
                )}
              </div>
            ))}
          </div>

          {/* Step labels */}
          <div className="flex justify-between mt-2 text-xs text-gray-500">
            <span>Date & Time</span>
            <span>Guest Details</span>
            <span>Review & Pay</span>
          </div>
        </div>
      </motion.div>

      <div className="px-6 py-6">
        {/* Activity summary */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-white rounded-2xl p-4 mb-6 shadow-sm border border-gray-100"
        >
          <div className="flex items-center space-x-4">
            <div className="w-16 h-16 bg-gradient-to-br from-primary-100 to-adventure-100 rounded-2xl flex items-center justify-center text-3xl">
              {activity.image}
            </div>
            <div className="flex-1">
              <h3 className="font-semibold text-gray-900 mb-1">{activity.title}</h3>
              <div className="flex items-center text-gray-500 text-sm mb-2">
                <MapPin className="w-4 h-4 mr-1" />
                <span>{activity.location}</span>
              </div>
              <div className="flex items-center space-x-4 text-sm text-gray-500">
                <div className="flex items-center">
                  <Clock className="w-4 h-4 mr-1" />
                  <span>{activity.duration}</span>
                </div>
                <span>৳{activity.price.toLocaleString()} per person</span>
              </div>
            </div>
          </div>
        </motion.div>

        {/* Step content */}
        <motion.div
          key={currentStep}
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.3 }}
        >
          {/* Step 1: Date & Time Selection */}
          {currentStep === 1 && (
            <div className="space-y-6">
              {/* Date Selection */}
              <div>
                <h3 className="font-semibold text-gray-900 mb-4">Select Date</h3>
                <div className="grid grid-cols-7 gap-2">
                  {availableDates.slice(0, 21).map((date, index) => (
                    <button
                      key={index}
                      onClick={() => setSelectedDate(date)}
                      className={`p-3 text-center rounded-xl transition-all duration-200 ${
                        isDateSelected(date)
                          ? 'bg-primary-600 text-white shadow-primary'
                          : 'bg-white hover:bg-gray-50 border border-gray-200'
                      }`}
                    >
                      <div className="text-xs text-gray-500">
                        {date.toLocaleDateString('en-US', { weekday: 'short' })}
                      </div>
                      <div className="text-sm font-medium">
                        {date.getDate()}
                      </div>
                    </button>
                  ))}
                </div>
              </div>

              {/* Time Selection */}
              {selectedDate && (
                <div>
                  <h3 className="font-semibold text-gray-900 mb-4">
                    Available Times for {formatDate(selectedDate)}
                  </h3>
                  <div className="space-y-3">
                    {availableTimes.map((timeSlot, index) => (
                      <button
                        key={index}
                        onClick={() => timeSlot.available && setSelectedTime(timeSlot.time)}
                        disabled={!timeSlot.available}
                        className={`w-full flex items-center justify-between p-4 rounded-xl transition-all duration-200 ${
                          selectedTime === timeSlot.time
                            ? 'bg-primary-600 text-white shadow-primary'
                            : timeSlot.available
                              ? 'bg-white hover:bg-gray-50 border border-gray-200'
                              : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        }`}
                      >
                        <span className="font-medium">{timeSlot.time}</span>
                        <span className="text-sm">
                          {timeSlot.available 
                            ? `${timeSlot.slots} slots available`
                            : 'Fully booked'
                          }
                        </span>
                      </button>
                    ))}
                  </div>
                </div>
              )}

              {/* Guest count */}
              {selectedTime && (
                <div>
                  <h3 className="font-semibold text-gray-900 mb-4">Number of Guests</h3>
                  <div className="bg-white rounded-2xl p-4 border border-gray-200">
                    <div className="flex items-center justify-between">
                      <div>
                        <span className="font-medium text-gray-900">Guests</span>
                        <p className="text-sm text-gray-500">Maximum {activity.maxGuests} guests</p>
                      </div>
                      <div className="flex items-center space-x-4">
                        <button
                          onClick={() => handleGuestChange(-1)}
                          disabled={guests <= 1}
                          className="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                          <Minus className="w-4 h-4" />
                        </button>
                        <span className="font-semibold text-lg min-w-[2rem] text-center">{guests}</span>
                        <button
                          onClick={() => handleGuestChange(1)}
                          disabled={guests >= activity.maxGuests}
                          className="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                        >
                          <Plus className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Step 2: Guest Details */}
          {currentStep === 2 && (
            <div className="space-y-6">
              <h3 className="font-semibold text-gray-900 mb-4">Guest Information</h3>
              
              <div className="bg-white rounded-2xl p-6 space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Full Name *
                  </label>
                  <div className="relative">
                    <User className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                    <input
                      type="text"
                      value={guestDetails.name}
                      onChange={(e) => handleInputChange('name', e.target.value)}
                      className="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Enter your full name"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Email Address *
                  </label>
                  <div className="relative">
                    <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                    <input
                      type="email"
                      value={guestDetails.email}
                      onChange={(e) => handleInputChange('email', e.target.value)}
                      className="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Enter your email"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number *
                  </label>
                  <div className="relative">
                    <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                    <input
                      type="tel"
                      value={guestDetails.phone}
                      onChange={(e) => handleInputChange('phone', e.target.value)}
                      className="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Enter your phone number"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Emergency Contact
                  </label>
                  <input
                    type="text"
                    value={guestDetails.emergencyContact}
                    onChange={(e) => handleInputChange('emergencyContact', e.target.value)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Emergency contact number"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Special Requests
                  </label>
                  <textarea
                    value={guestDetails.specialRequests}
                    onChange={(e) => handleInputChange('specialRequests', e.target.value)}
                    rows={3}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Any special requests or dietary requirements..."
                  />
                </div>
              </div>

              {/* Safety notice */}
              <div className="bg-orange-50 border border-orange-200 rounded-xl p-4">
                <div className="flex items-start space-x-3">
                  <AlertCircle className="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" />
                  <div>
                    <h4 className="font-medium text-orange-900 mb-1">Safety Requirements</h4>
                    <p className="text-sm text-orange-700">
                      Please ensure you meet all health requirements. You'll need to sign a waiver before the activity.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Step 3: Review & Summary */}
          {currentStep === 3 && (
            <div className="space-y-6">
              <h3 className="font-semibold text-gray-900 mb-4">Booking Summary</h3>

              {/* Booking details */}
              <div className="bg-white rounded-2xl p-6 space-y-4">
                <div className="flex items-center justify-between py-2 border-b border-gray-100">
                  <span className="text-gray-600">Date</span>
                  <span className="font-medium">{selectedDate && formatDate(selectedDate)}</span>
                </div>
                <div className="flex items-center justify-between py-2 border-b border-gray-100">
                  <span className="text-gray-600">Time</span>
                  <span className="font-medium">{selectedTime}</span>
                </div>
                <div className="flex items-center justify-between py-2 border-b border-gray-100">
                  <span className="text-gray-600">Guests</span>
                  <span className="font-medium">{guests} {guests === 1 ? 'person' : 'people'}</span>
                </div>
                <div className="flex items-center justify-between py-2">
                  <span className="text-gray-600">Contact</span>
                  <span className="font-medium">{guestDetails.name}</span>
                </div>
              </div>

              {/* Price breakdown */}
              <div className="bg-white rounded-2xl p-6 space-y-4">
                <h4 className="font-medium text-gray-900">Price Breakdown</h4>
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-gray-600">
                      ৳{activity.price.toLocaleString()} × {guests} {guests === 1 ? 'person' : 'people'}
                    </span>
                    <span>৳{pricing.subtotal.toLocaleString()}</span>
                  </div>
                  {pricing.discount > 0 && (
                    <div className="flex items-center justify-between text-green-600">
                      <span>Discount ({activity.discount}%)</span>
                      <span>-৳{pricing.discount.toLocaleString()}</span>
                    </div>
                  )}
                  <div className="flex items-center justify-between text-gray-600">
                    <span>Service fee</span>
                    <span>৳{pricing.serviceFee.toLocaleString()}</span>
                  </div>
                  <div className="border-t border-gray-200 pt-3">
                    <div className="flex items-center justify-between text-lg font-semibold">
                      <span>Total</span>
                      <span>৳{pricing.total.toLocaleString()}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}
        </motion.div>
      </div>

      {/* Fixed bottom action bar */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-4">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm text-gray-500">Total</p>
            <p className="text-xl font-bold text-gray-900">৳{pricing.total.toLocaleString()}</p>
          </div>
          <button
            onClick={nextStep}
            disabled={!canProceed()}
            className={`px-8 py-3 rounded-xl font-semibold transition-all duration-200 ${
              canProceed()
                ? 'bg-primary-600 hover:bg-primary-700 text-white shadow-primary'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            }`}
          >
            {currentStep === 3 ? 'Proceed to Payment' : 'Continue'}
          </button>
        </div>
      </div>

      {/* Bottom padding for fixed action bar */}
      <div className="h-20"></div>
    </div>
  );
};

export default BookingScreen;