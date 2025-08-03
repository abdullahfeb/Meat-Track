import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, 
  User,
  Settings,
  History,
  Heart,
  HelpCircle,
  LogOut,
  Edit,
  Camera,
  Calendar,
  MapPin,
  Star,
  CheckCircle,
  Clock,
  Shield,
  Bell,
  Globe,
  CreditCard
} from 'lucide-react';

const ProfileScreen = () => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('bookings');

  const user = {
    name: 'Sarah Ahmed',
    email: 'sarah.ahmed@email.com',
    phone: '+880 1711-123456',
    joinDate: 'January 2024',
    totalBookings: 12,
    adventuresCompleted: 8,
    avatar: '👩‍💼'
  };

  const bookings = [
    {
      id: 1,
      activity: 'Skydiving Experience',
      location: "Cox's Bazar",
      date: '2024-01-15',
      status: 'completed',
      rating: 5,
      price: 12000,
      image: '🪂'
    },
    {
      id: 2,
      activity: 'Scuba Diving Adventure',
      location: 'Saint Martin Island',
      date: '2024-01-20',
      status: 'completed',
      rating: 4,
      price: 8500,
      image: '🤿'
    },
    {
      id: 3,
      activity: 'Hiking & Trekking',
      location: 'Bandarban Hills',
      date: '2024-02-05',
      status: 'upcoming',
      price: 3500,
      image: '🥾'
    },
    {
      id: 4,
      activity: 'Paragliding',
      location: 'Rangamati',
      date: '2024-02-15',
      status: 'upcoming',
      price: 7800,
      image: '🪁'
    }
  ];

  const favorites = [
    {
      id: 1,
      title: 'Bungee Jumping',
      location: 'Sylhet',
      price: 5500,
      rating: 4.6,
      image: '🏃‍♂️'
    },
    {
      id: 2,
      title: 'White Water Rafting',
      location: 'Rangpur',
      price: 4200,
      rating: 4.5,
      image: '🚣'
    },
    {
      id: 3,
      title: 'Rock Climbing',
      location: 'Chittagong',
      price: 6000,
      rating: 4.7,
      image: '🧗‍♂️'
    }
  ];

  const settingsOptions = [
    { id: 'notifications', title: 'Notifications', icon: Bell, description: 'Manage your notification preferences' },
    { id: 'language', title: 'Language', icon: Globe, description: 'English' },
    { id: 'payment', title: 'Payment Methods', icon: CreditCard, description: 'Manage saved payment methods' },
    { id: 'privacy', title: 'Privacy & Security', icon: Shield, description: 'Control your privacy settings' },
    { id: 'help', title: 'Help & Support', icon: HelpCircle, description: 'Get help and contact support' },
  ];

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed':
        return 'text-green-600 bg-green-100';
      case 'upcoming':
        return 'text-blue-600 bg-blue-100';
      case 'cancelled':
        return 'text-red-600 bg-red-100';
      default:
        return 'text-gray-600 bg-gray-100';
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric', 
      year: 'numeric' 
    });
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
          <div className="flex items-center space-x-3">
            <button 
              onClick={() => navigate('/home')}
              className="p-2 text-gray-400 hover:text-gray-600 -ml-2"
            >
              <ArrowLeft className="w-6 h-6" />
            </button>
            <h1 className="text-xl font-display font-semibold text-gray-900">Profile</h1>
          </div>
        </div>
      </motion.div>

      {/* Profile Header */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white mx-6 mt-6 rounded-2xl p-6 shadow-sm border border-gray-100"
      >
        <div className="flex items-center space-x-4">
          <div className="relative">
            <div className="w-20 h-20 bg-gradient-to-br from-primary-100 to-adventure-100 rounded-full flex items-center justify-center text-3xl">
              {user.avatar}
            </div>
            <button className="absolute -bottom-1 -right-1 w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white shadow-lg">
              <Camera className="w-4 h-4" />
            </button>
          </div>
          <div className="flex-1">
            <div className="flex items-center space-x-2 mb-1">
              <h2 className="text-xl font-semibold text-gray-900">{user.name}</h2>
              <button className="p-1 text-gray-400 hover:text-gray-600">
                <Edit className="w-4 h-4" />
              </button>
            </div>
            <p className="text-gray-600 mb-2">{user.email}</p>
            <p className="text-sm text-gray-500">Member since {user.joinDate}</p>
          </div>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100">
          <div className="text-center">
            <div className="text-2xl font-bold text-primary-600">{user.totalBookings}</div>
            <div className="text-sm text-gray-500">Total Bookings</div>
          </div>
          <div className="text-center">
            <div className="text-2xl font-bold text-adventure-600">{user.adventuresCompleted}</div>
            <div className="text-sm text-gray-500">Adventures Completed</div>
          </div>
        </div>
      </motion.div>

      {/* Tab Navigation */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="px-6 mt-6"
      >
        <div className="flex space-x-1 bg-gray-100 rounded-xl p-1">
          {[
            { id: 'bookings', name: 'Bookings', icon: History },
            { id: 'favorites', name: 'Favorites', icon: Heart },
            { id: 'settings', name: 'Settings', icon: Settings }
          ].map((tab) => {
            const IconComponent = tab.icon;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`flex-1 flex items-center justify-center space-x-2 py-3 px-4 rounded-lg text-sm font-medium transition-all duration-200 ${
                  activeTab === tab.id
                    ? 'bg-white text-primary-600 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                }`}
              >
                <IconComponent className="w-4 h-4" />
                <span>{tab.name}</span>
              </button>
            );
          })}
        </div>
      </motion.div>

      {/* Tab Content */}
      <div className="px-6 mt-6 pb-6">
        {/* Bookings Tab */}
        {activeTab === 'bookings' && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-4"
          >
            <h3 className="font-semibold text-gray-900">Booking History</h3>
            {bookings.map((booking, index) => (
              <motion.div
                key={booking.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 * index }}
                className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
              >
                <div className="flex items-start space-x-4">
                  <div className="w-16 h-16 bg-gradient-to-br from-primary-100 to-adventure-100 rounded-2xl flex items-center justify-center text-2xl">
                    {booking.image}
                  </div>
                  <div className="flex-1">
                    <div className="flex items-start justify-between mb-2">
                      <div>
                        <h4 className="font-semibold text-gray-900">{booking.activity}</h4>
                        <div className="flex items-center text-gray-500 text-sm mt-1">
                          <MapPin className="w-4 h-4 mr-1" />
                          <span>{booking.location}</span>
                        </div>
                      </div>
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(booking.status)}`}>
                        {booking.status}
                      </span>
                    </div>
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-4 text-sm text-gray-500">
                        <div className="flex items-center">
                          <Calendar className="w-4 h-4 mr-1" />
                          <span>{formatDate(booking.date)}</span>
                        </div>
                        <span>৳{booking.price.toLocaleString()}</span>
                      </div>
                      {booking.status === 'completed' && booking.rating && (
                        <div className="flex items-center text-yellow-500">
                          <Star className="w-4 h-4 fill-current" />
                          <span className="ml-1 text-sm font-medium text-gray-900">{booking.rating}</span>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </motion.div>
        )}

        {/* Favorites Tab */}
        {activeTab === 'favorites' && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-4"
          >
            <h3 className="font-semibold text-gray-900">Favorite Activities</h3>
            {favorites.map((favorite, index) => (
              <motion.div
                key={favorite.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 * index }}
                whileHover={{ scale: 1.02 }}
                onClick={() => navigate(`/activity/${favorite.id}`)}
                className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 cursor-pointer"
              >
                <div className="flex items-center space-x-4">
                  <div className="w-16 h-16 bg-gradient-to-br from-primary-100 to-adventure-100 rounded-2xl flex items-center justify-center text-2xl">
                    {favorite.image}
                  </div>
                  <div className="flex-1">
                    <h4 className="font-semibold text-gray-900 mb-1">{favorite.title}</h4>
                    <div className="flex items-center text-gray-500 text-sm mb-2">
                      <MapPin className="w-4 h-4 mr-1" />
                      <span>{favorite.location}</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="font-medium text-gray-900">৳{favorite.price.toLocaleString()}</span>
                      <div className="flex items-center text-yellow-500">
                        <Star className="w-4 h-4 fill-current" />
                        <span className="ml-1 text-sm font-medium text-gray-900">{favorite.rating}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </motion.div>
        )}

        {/* Settings Tab */}
        {activeTab === 'settings' && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-4"
          >
            <h3 className="font-semibold text-gray-900">Account Settings</h3>
            <div className="space-y-3">
              {settingsOptions.map((option, index) => {
                const IconComponent = option.icon;
                return (
                  <motion.button
                    key={option.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.1 * index }}
                    className="w-full bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:bg-gray-50 transition-colors"
                  >
                    <div className="flex items-center space-x-4">
                      <div className="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                        <IconComponent className="w-5 h-5 text-gray-600" />
                      </div>
                      <div className="flex-1 text-left">
                        <h4 className="font-medium text-gray-900">{option.title}</h4>
                        <p className="text-sm text-gray-500">{option.description}</p>
                      </div>
                    </div>
                  </motion.button>
                );
              })}
            </div>

            {/* Logout Button */}
            <motion.button
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.6 }}
              onClick={() => navigate('/login')}
              className="w-full bg-red-50 border border-red-200 rounded-2xl p-4 hover:bg-red-100 transition-colors mt-6"
            >
              <div className="flex items-center justify-center space-x-2 text-red-600">
                <LogOut className="w-5 h-5" />
                <span className="font-medium">Sign Out</span>
              </div>
            </motion.button>
          </motion.div>
        )}
      </div>

      {/* Bottom padding for navigation */}
      <div className="h-20"></div>
    </div>
  );
};

export default ProfileScreen;