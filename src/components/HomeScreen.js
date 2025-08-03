import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  Search, 
  MapPin, 
  Bell, 
  User, 
  Filter,
  Star,
  Clock,
  Users,
  Heart,
  Plane,
  Waves,
  Mountain,
  Compass,
  Grid3X3,
  Calendar
} from 'lucide-react';

const HomeScreen = () => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [location] = useState('Dhaka, Bangladesh');

  const adventureCategories = [
    { id: 1, name: 'Skydiving', icon: Plane, color: 'bg-blue-500', count: 15 },
    { id: 2, name: 'Scuba Diving', icon: Waves, color: 'bg-cyan-500', count: 23 },
    { id: 3, name: 'Hiking', icon: Mountain, color: 'bg-green-500', count: 42 },
    { id: 4, name: 'Safari', icon: Compass, color: 'bg-orange-500', count: 18 },
  ];

  const featuredAdventures = [
    {
      id: 1,
      title: "Skydiving Experience",
      location: "Cox's Bazar",
      price: 12000,
      rating: 4.8,
      reviews: 156,
      duration: "3 hours",
      groupSize: "1-4 people",
      image: "skydiving",
      category: "Extreme Sports",
      discount: 20
    },
    {
      id: 2,
      title: "Scuba Diving Adventure",
      location: "Saint Martin Island",
      price: 8500,
      rating: 4.9,
      reviews: 203,
      duration: "5 hours",
      groupSize: "2-8 people",
      image: "scuba",
      category: "Water Sports"
    },
    {
      id: 3,
      title: "Hiking & Trekking",
      location: "Bandarban Hills",
      price: 3500,
      rating: 4.7,
      reviews: 89,
      duration: "2 days",
      groupSize: "5-15 people",
      image: "hiking",
      category: "Adventure"
    }
  ];

  const nearbyAdventures = [
    {
      id: 4,
      title: "Bungee Jumping",
      location: "2.5 km away",
      price: 5500,
      rating: 4.6,
      quickBook: true
    },
    {
      id: 5,
      title: "Paragliding",
      location: "1.8 km away",
      price: 7800,
      rating: 4.8,
      quickBook: true
    },
    {
      id: 6,
      title: "White Water Rafting",
      location: "5.2 km away",
      price: 4200,
      rating: 4.5,
      quickBook: false
    }
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white shadow-sm border-b border-gray-100"
      >
        <div className="px-6 py-4">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-gradient-to-r from-primary-500 to-adventure-500 rounded-xl flex items-center justify-center">
                <Mountain className="w-6 h-6 text-white" />
              </div>
              <div>
                <p className="text-sm text-gray-500">Good morning!</p>
                <h1 className="text-lg font-semibold text-gray-900">Ready for adventure?</h1>
              </div>
            </div>
            <div className="flex items-center space-x-3">
              <button className="p-2 text-gray-400 hover:text-gray-600 relative">
                <Bell className="w-6 h-6" />
                <span className="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
              </button>
              <button 
                onClick={() => navigate('/profile')}
                className="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center"
              >
                <User className="w-5 h-5 text-primary-600" />
              </button>
            </div>
          </div>

          {/* Location */}
          <div className="flex items-center text-gray-600 mb-4">
            <MapPin className="w-4 h-4 mr-2" />
            <span className="text-sm">{location}</span>
          </div>

          {/* Search Bar */}
          <div className="relative">
            <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search adventures, activities..."
              className="w-full pl-12 pr-12 py-3 bg-gray-100 border-0 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all duration-200"
              onFocus={() => navigate('/search')}
            />
            <button className="absolute right-3 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600">
              <Filter className="w-5 h-5" />
            </button>
          </div>
        </div>
      </motion.div>

      <div className="px-6 py-6 space-y-8">
        {/* Categories */}
        <motion.section
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
        >
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-display font-semibold text-gray-900">Categories</h2>
            <button 
              onClick={() => navigate('/categories')}
              className="flex items-center text-primary-600 text-sm font-medium"
            >
              <span>View all</span>
              <Grid3X3 className="w-4 h-4 ml-1" />
            </button>
          </div>
          <div className="grid grid-cols-2 gap-4">
            {adventureCategories.map((category) => {
              const IconComponent = category.icon;
              return (
                <motion.div
                  key={category.id}
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  onClick={() => navigate('/categories')}
                  className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 cursor-pointer"
                >
                  <div className={`w-12 h-12 ${category.color} rounded-xl flex items-center justify-center mb-4`}>
                    <IconComponent className="w-6 h-6 text-white" />
                  </div>
                  <h3 className="font-semibold text-gray-900 mb-1">{category.name}</h3>
                  <p className="text-sm text-gray-500">{category.count} activities</p>
                </motion.div>
              );
            })}
          </div>
        </motion.section>

        {/* Featured Adventures */}
        <motion.section
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
        >
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-display font-semibold text-gray-900">Featured Adventures</h2>
            <button className="text-primary-600 text-sm font-medium">View all</button>
          </div>
          <div className="space-y-4">
            {featuredAdventures.map((adventure, index) => (
              <motion.div
                key={adventure.id}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.1 * index }}
                whileHover={{ scale: 1.02 }}
                onClick={() => navigate(`/activity/${adventure.id}`)}
                className="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 cursor-pointer"
              >
                <div className="relative">
                  <div className="h-48 bg-gradient-to-br from-primary-400 to-adventure-400 flex items-center justify-center">
                    <div className="text-center text-white">
                      <div className="text-4xl mb-2">🏔️</div>
                      <p className="text-sm opacity-80">{adventure.image}</p>
                    </div>
                  </div>
                  {adventure.discount && (
                    <div className="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-lg text-xs font-medium">
                      {adventure.discount}% OFF
                    </div>
                  )}
                  <button className="absolute top-3 right-3 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                    <Heart className="w-4 h-4 text-white" />
                  </button>
                </div>
                <div className="p-4">
                  <div className="flex items-start justify-between mb-2">
                    <div>
                      <h3 className="font-semibold text-gray-900 mb-1">{adventure.title}</h3>
                      <div className="flex items-center text-gray-500 text-sm">
                        <MapPin className="w-4 h-4 mr-1" />
                        <span>{adventure.location}</span>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-lg font-bold text-gray-900">৳{adventure.price.toLocaleString()}</p>
                      <p className="text-xs text-gray-500">per person</p>
                    </div>
                  </div>
                  <div className="flex items-center justify-between text-sm text-gray-500 mb-3">
                    <div className="flex items-center">
                      <Clock className="w-4 h-4 mr-1" />
                      <span>{adventure.duration}</span>
                    </div>
                    <div className="flex items-center">
                      <Users className="w-4 h-4 mr-1" />
                      <span>{adventure.groupSize}</span>
                    </div>
                  </div>
                  <div className="flex items-center justify-between">
                    <div className="flex items-center">
                      <div className="flex items-center text-yellow-500">
                        <Star className="w-4 h-4 fill-current" />
                        <span className="ml-1 text-sm font-medium text-gray-900">{adventure.rating}</span>
                      </div>
                      <span className="text-sm text-gray-500 ml-1">({adventure.reviews} reviews)</span>
                    </div>
                    <span className="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded-full">
                      {adventure.category}
                    </span>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        </motion.section>

        {/* Nearby Adventures */}
        <motion.section
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3 }}
        >
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-xl font-display font-semibold text-gray-900">Near You</h2>
            <button className="text-primary-600 text-sm font-medium">View all</button>
          </div>
          <div className="space-y-3">
            {nearbyAdventures.map((adventure) => (
              <motion.div
                key={adventure.id}
                whileHover={{ scale: 1.01 }}
                onClick={() => navigate(`/activity/${adventure.id}`)}
                className="bg-white rounded-xl p-4 shadow-sm border border-gray-100 cursor-pointer"
              >
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <h3 className="font-medium text-gray-900 mb-1">{adventure.title}</h3>
                    <div className="flex items-center text-gray-500 text-sm mb-2">
                      <MapPin className="w-3 h-3 mr-1" />
                      <span>{adventure.location}</span>
                    </div>
                    <div className="flex items-center">
                      <Star className="w-4 h-4 fill-current text-yellow-500" />
                      <span className="ml-1 text-sm font-medium text-gray-900">{adventure.rating}</span>
                      <span className="text-sm text-gray-500 ml-2">৳{adventure.price.toLocaleString()}</span>
                    </div>
                  </div>
                  {adventure.quickBook && (
                    <button className="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                      Quick Book
                    </button>
                  )}
                </div>
              </motion.div>
            ))}
          </div>
        </motion.section>
      </div>

      {/* Bottom Navigation */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.4 }}
        className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-4"
      >
        <div className="flex items-center justify-around">
          <button className="flex flex-col items-center space-y-1 text-primary-600">
            <Mountain className="w-6 h-6" />
            <span className="text-xs font-medium">Home</span>
          </button>
          <button 
            onClick={() => navigate('/search')}
            className="flex flex-col items-center space-y-1 text-gray-400"
          >
            <Search className="w-6 h-6" />
            <span className="text-xs">Search</span>
          </button>
          <button 
            onClick={() => navigate('/categories')}
            className="flex flex-col items-center space-y-1 text-gray-400"
          >
            <Grid3X3 className="w-6 h-6" />
            <span className="text-xs">Categories</span>
          </button>
          <button className="flex flex-col items-center space-y-1 text-gray-400">
            <Calendar className="w-6 h-6" />
            <span className="text-xs">Bookings</span>
          </button>
          <button 
            onClick={() => navigate('/profile')}
            className="flex flex-col items-center space-y-1 text-gray-400"
          >
            <User className="w-6 h-6" />
            <span className="text-xs">Profile</span>
          </button>
        </div>
      </motion.div>

      {/* Bottom padding for fixed navigation */}
      <div className="h-20"></div>
    </div>
  );
};

export default HomeScreen;