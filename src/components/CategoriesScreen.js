import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, 
  Search, 
  Filter,
  Plane,
  Waves,
  Mountain,
  Compass,
  Wind,
  Zap,
  TreePine,
  Camera,
  Star,
  MapPin
} from 'lucide-react';

const CategoriesScreen = () => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');

  const categories = [
    { id: 'all', name: 'All', icon: Compass, color: 'bg-gray-500' },
    { id: 'extreme', name: 'Extreme Sports', icon: Plane, color: 'bg-red-500' },
    { id: 'water', name: 'Water Sports', icon: Waves, color: 'bg-blue-500' },
    { id: 'land', name: 'Land Adventures', icon: Mountain, color: 'bg-green-500' },
    { id: 'air', name: 'Air Sports', icon: Wind, color: 'bg-purple-500' },
    { id: 'wildlife', name: 'Wildlife', icon: TreePine, color: 'bg-orange-500' },
    { id: 'adventure', name: 'Adventure Tours', icon: Camera, color: 'bg-indigo-500' },
  ];

  const allActivities = [
    {
      id: 1,
      name: 'Skydiving',
      category: 'extreme',
      icon: '🪂',
      description: 'Experience the ultimate adrenaline rush',
      activities: 15,
      priceRange: '10,000 - 15,000',
      popularity: 4.8,
      difficulty: 'Extreme'
    },
    {
      id: 2,
      name: 'Scuba Diving',
      category: 'water',
      icon: '🤿',
      description: 'Explore underwater wonders',
      activities: 23,
      priceRange: '6,000 - 12,000',
      popularity: 4.9,
      difficulty: 'Moderate'
    },
    {
      id: 3,
      name: 'Bungee Jumping',
      category: 'extreme',
      icon: '🏃‍♂️',
      description: 'Take the leap of faith',
      activities: 8,
      priceRange: '4,000 - 8,000',
      popularity: 4.7,
      difficulty: 'Extreme'
    },
    {
      id: 4,
      name: 'Hiking/Trekking',
      category: 'land',
      icon: '🥾',
      description: 'Discover scenic mountain trails',
      activities: 42,
      priceRange: '2,000 - 5,000',
      popularity: 4.6,
      difficulty: 'Easy'
    },
    {
      id: 5,
      name: 'Safari Tours',
      category: 'wildlife',
      icon: '🦁',
      description: 'Witness wildlife in their habitat',
      activities: 18,
      priceRange: '8,000 - 20,000',
      popularity: 4.8,
      difficulty: 'Easy'
    },
    {
      id: 6,
      name: 'Paragliding',
      category: 'air',
      icon: '🪁',
      description: 'Soar through the skies',
      activities: 12,
      priceRange: '6,000 - 10,000',
      popularity: 4.7,
      difficulty: 'Moderate'
    },
    {
      id: 7,
      name: 'White-water Rafting',
      category: 'water',
      icon: '🚣',
      description: 'Navigate through rapids',
      activities: 16,
      priceRange: '3,000 - 7,000',
      popularity: 4.5,
      difficulty: 'Moderate'
    },
    {
      id: 8,
      name: 'Zip Lining',
      category: 'adventure',
      icon: '🎢',
      description: 'Zip through forest canopies',
      activities: 14,
      priceRange: '2,500 - 5,000',
      popularity: 4.4,
      difficulty: 'Easy'
    },
    {
      id: 9,
      name: 'Rock Climbing',
      category: 'land',
      icon: '🧗‍♂️',
      description: 'Conquer vertical challenges',
      activities: 11,
      priceRange: '3,500 - 8,000',
      popularity: 4.6,
      difficulty: 'Hard'
    },
    {
      id: 10,
      name: 'Cave Exploration',
      category: 'adventure',
      icon: '🕳️',
      description: 'Discover hidden underground worlds',
      activities: 7,
      priceRange: '4,000 - 9,000',
      popularity: 4.3,
      difficulty: 'Moderate'
    }
  ];

  const filteredActivities = allActivities.filter(activity => {
    const matchesCategory = selectedCategory === 'all' || activity.category === selectedCategory;
    const matchesSearch = activity.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         activity.description.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  const getDifficultyColor = (difficulty) => {
    switch (difficulty) {
      case 'Easy': return 'text-green-600 bg-green-100';
      case 'Moderate': return 'text-yellow-600 bg-yellow-100';
      case 'Hard': return 'text-orange-600 bg-orange-100';
      case 'Extreme': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
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
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center space-x-3">
              <button 
                onClick={() => navigate('/home')}
                className="p-2 text-gray-400 hover:text-gray-600 -ml-2"
              >
                <ArrowLeft className="w-6 h-6" />
              </button>
              <h1 className="text-xl font-display font-semibold text-gray-900">Adventure Categories</h1>
            </div>
            <button className="p-2 text-gray-400 hover:text-gray-600">
              <Filter className="w-6 h-6" />
            </button>
          </div>

          {/* Search Bar */}
          <div className="relative">
            <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search activities..."
              className="w-full pl-12 pr-4 py-3 bg-gray-100 border-0 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all duration-200"
            />
          </div>
        </div>
      </motion.div>

      <div className="px-6 py-6">
        {/* Category Filter */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.1 }}
          className="mb-6"
        >
          <div className="flex space-x-3 overflow-x-auto pb-2">
            {categories.map((category) => {
              const IconComponent = category.icon;
              const isSelected = selectedCategory === category.id;
              
              return (
                <motion.button
                  key={category.id}
                  whileHover={{ scale: 1.05 }}
                  whileTap={{ scale: 0.95 }}
                  onClick={() => setSelectedCategory(category.id)}
                  className={`flex items-center space-x-2 px-4 py-2 rounded-xl whitespace-nowrap transition-all duration-200 ${
                    isSelected 
                      ? 'bg-primary-600 text-white shadow-primary' 
                      : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'
                  }`}
                >
                  <IconComponent className="w-4 h-4" />
                  <span className="text-sm font-medium">{category.name}</span>
                </motion.button>
              );
            })}
          </div>
        </motion.div>

        {/* Activities Grid */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
          className="space-y-4"
        >
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-semibold text-gray-900">
              {filteredActivities.length} Activities Found
            </h2>
            <button className="text-primary-600 text-sm font-medium">Sort by popularity</button>
          </div>

          <div className="grid gap-4">
            {filteredActivities.map((activity, index) => (
              <motion.div
                key={activity.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 * index }}
                whileHover={{ scale: 1.02 }}
                onClick={() => navigate(`/activity/${activity.id}`)}
                className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 cursor-pointer card-hover"
              >
                <div className="flex items-start space-x-4">
                  {/* Activity Icon */}
                  <div className="w-16 h-16 bg-gradient-to-br from-primary-100 to-adventure-100 rounded-2xl flex items-center justify-center text-3xl">
                    {activity.icon}
                  </div>

                  {/* Activity Info */}
                  <div className="flex-1">
                    <div className="flex items-start justify-between mb-2">
                      <div>
                        <h3 className="font-semibold text-gray-900 text-lg mb-1">{activity.name}</h3>
                        <p className="text-gray-600 text-sm mb-2">{activity.description}</p>
                      </div>
                      <div className="flex items-center text-yellow-500">
                        <Star className="w-4 h-4 fill-current" />
                        <span className="ml-1 text-sm font-medium text-gray-900">{activity.popularity}</span>
                      </div>
                    </div>

                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-4 text-sm text-gray-500">
                        <div className="flex items-center">
                          <MapPin className="w-4 h-4 mr-1" />
                          <span>{activity.activities} locations</span>
                        </div>
                        <span>৳{activity.priceRange}</span>
                      </div>
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${getDifficultyColor(activity.difficulty)}`}>
                        {activity.difficulty}
                      </span>
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>

          {filteredActivities.length === 0 && (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              className="text-center py-12"
            >
              <div className="text-6xl mb-4">🔍</div>
              <h3 className="text-lg font-semibold text-gray-900 mb-2">No activities found</h3>
              <p className="text-gray-500">Try adjusting your search or category filter</p>
            </motion.div>
          )}
        </motion.div>
      </div>

      {/* Bottom padding for navigation */}
      <div className="h-20"></div>
    </div>
  );
};

export default CategoriesScreen;