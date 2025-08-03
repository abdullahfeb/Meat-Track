import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, 
  Search, 
  Filter,
  MapPin,
  Star,
  Clock,
  Users,
  X
} from 'lucide-react';

const SearchScreen = () => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [filters, setFilters] = useState({
    priceRange: 'all',
    difficulty: 'all',
    duration: 'all',
    rating: 'all'
  });
  const [showFilters, setShowFilters] = useState(false);

  const recentSearches = [
    'Skydiving Cox\'s Bazar',
    'Scuba diving',
    'Hiking Bandarban',
    'Safari Sundarbans'
  ];

  const popularSearches = [
    'Adventure tours',
    'Water sports',
    'Extreme sports',
    'Wildlife safari',
    'Mountain climbing'
  ];

  const searchResults = [
    {
      id: 1,
      title: "Skydiving Experience",
      location: "Cox's Bazar",
      price: 12000,
      rating: 4.8,
      reviews: 156,
      duration: "3 hours",
      groupSize: "1-4 people",
      difficulty: "Extreme",
      image: "🪂"
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
      difficulty: "Moderate",
      image: "🤿"
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
      difficulty: "Easy",
      image: "🥾"
    }
  ];

  const filteredResults = searchResults.filter(result => 
    result.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
    result.location.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const clearFilters = () => {
    setFilters({
      priceRange: 'all',
      difficulty: 'all',
      duration: 'all',
      rating: 'all'
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
          <div className="flex items-center space-x-3 mb-4">
            <button 
              onClick={() => navigate('/home')}
              className="p-2 text-gray-400 hover:text-gray-600 -ml-2"
            >
              <ArrowLeft className="w-6 h-6" />
            </button>
            <div className="flex-1 relative">
              <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search adventures, locations..."
                className="w-full pl-12 pr-4 py-3 bg-gray-100 border-0 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all duration-200"
                autoFocus
              />
            </div>
            <button 
              onClick={() => setShowFilters(!showFilters)}
              className="p-2 text-gray-400 hover:text-gray-600"
            >
              <Filter className="w-6 h-6" />
            </button>
          </div>
        </div>
      </motion.div>

      {/* Filters Panel */}
      {showFilters && (
        <motion.div
          initial={{ opacity: 0, height: 0 }}
          animate={{ opacity: 1, height: 'auto' }}
          exit={{ opacity: 0, height: 0 }}
          className="bg-white border-b border-gray-100 px-6 py-4"
        >
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-semibold text-gray-900">Filters</h3>
            <button 
              onClick={clearFilters}
              className="text-primary-600 text-sm font-medium"
            >
              Clear all
            </button>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
              <select 
                value={filters.priceRange}
                onChange={(e) => setFilters({...filters, priceRange: e.target.value})}
                className="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
                <option value="all">All Prices</option>
                <option value="low">Under ৳5,000</option>
                <option value="mid">৳5,000 - ৳10,000</option>
                <option value="high">Above ৳10,000</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
              <select 
                value={filters.difficulty}
                onChange={(e) => setFilters({...filters, difficulty: e.target.value})}
                className="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
                <option value="all">All Levels</option>
                <option value="easy">Easy</option>
                <option value="moderate">Moderate</option>
                <option value="hard">Hard</option>
                <option value="extreme">Extreme</option>
              </select>
            </div>
          </div>
        </motion.div>
      )}

      <div className="px-6 py-6">
        {/* Show search suggestions when no query */}
        {!searchQuery && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
          >
            {/* Recent Searches */}
            <div>
              <h3 className="font-semibold text-gray-900 mb-3">Recent Searches</h3>
              <div className="space-y-2">
                {recentSearches.map((search, index) => (
                  <button
                    key={index}
                    onClick={() => setSearchQuery(search)}
                    className="flex items-center justify-between w-full p-3 bg-white rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors"
                  >
                    <div className="flex items-center space-x-3">
                      <Clock className="w-4 h-4 text-gray-400" />
                      <span className="text-gray-700">{search}</span>
                    </div>
                    <X className="w-4 h-4 text-gray-400" />
                  </button>
                ))}
              </div>
            </div>

            {/* Popular Searches */}
            <div>
              <h3 className="font-semibold text-gray-900 mb-3">Popular Searches</h3>
              <div className="flex flex-wrap gap-2">
                {popularSearches.map((search, index) => (
                  <button
                    key={index}
                    onClick={() => setSearchQuery(search)}
                    className="px-4 py-2 bg-primary-100 text-primary-700 rounded-xl text-sm font-medium hover:bg-primary-200 transition-colors"
                  >
                    {search}
                  </button>
                ))}
              </div>
            </div>
          </motion.div>
        )}

        {/* Search Results */}
        {searchQuery && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-4"
          >
            <div className="flex items-center justify-between">
              <h3 className="font-semibold text-gray-900">
                {filteredResults.length} results for "{searchQuery}"
              </h3>
              <button className="text-primary-600 text-sm font-medium">Sort by</button>
            </div>

            {filteredResults.length > 0 ? (
              <div className="space-y-4">
                {filteredResults.map((result, index) => (
                  <motion.div
                    key={result.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.1 * index }}
                    whileHover={{ scale: 1.02 }}
                    onClick={() => navigate(`/activity/${result.id}`)}
                    className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 cursor-pointer"
                  >
                    <div className="flex space-x-4">
                      <div className="w-20 h-20 bg-gradient-to-br from-primary-100 to-adventure-100 rounded-2xl flex items-center justify-center text-3xl">
                        {result.image}
                      </div>
                      <div className="flex-1">
                        <h4 className="font-semibold text-gray-900 mb-1">{result.title}</h4>
                        <div className="flex items-center text-gray-500 text-sm mb-2">
                          <MapPin className="w-4 h-4 mr-1" />
                          <span>{result.location}</span>
                        </div>
                        <div className="flex items-center justify-between">
                          <div className="flex items-center space-x-4 text-sm text-gray-500">
                            <div className="flex items-center">
                              <Clock className="w-4 h-4 mr-1" />
                              <span>{result.duration}</span>
                            </div>
                            <div className="flex items-center">
                              <Users className="w-4 h-4 mr-1" />
                              <span>{result.groupSize}</span>
                            </div>
                          </div>
                          <div className="text-right">
                            <div className="flex items-center text-yellow-500 mb-1">
                              <Star className="w-4 h-4 fill-current" />
                              <span className="ml-1 text-sm font-medium text-gray-900">{result.rating}</span>
                              <span className="text-xs text-gray-500 ml-1">({result.reviews})</span>
                            </div>
                            <p className="text-lg font-bold text-gray-900">৳{result.price.toLocaleString()}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <div className="text-6xl mb-4">🔍</div>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">No results found</h3>
                <p className="text-gray-500">Try adjusting your search terms or filters</p>
              </div>
            )}
          </motion.div>
        )}
      </div>

      {/* Bottom padding for navigation */}
      <div className="h-20"></div>
    </div>
  );
};

export default SearchScreen;