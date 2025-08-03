import React, { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, 
  Heart,
  Share,
  Star,
  MapPin,
  Clock,
  Users,
  Shield,
  Calendar,
  Phone,
  MessageCircle,
  Camera,
  CheckCircle,
  AlertTriangle
} from 'lucide-react';

const ActivityDetailScreen = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [isFavorite, setIsFavorite] = useState(false);
  const [activeTab, setActiveTab] = useState('overview');

  // Mock activity data - in real app, this would come from API
  const activity = {
    id: 1,
    title: "Skydiving Experience",
    location: "Cox's Bazar, Bangladesh",
    price: 12000,
    originalPrice: 15000,
    discount: 20,
    rating: 4.8,
    reviews: 156,
    duration: "3 hours",
    groupSize: "1-4 people",
    difficulty: "Extreme",
    category: "Extreme Sports",
    images: ["🪂", "🏔️", "☁️"],
    description: "Experience the ultimate adrenaline rush with our professional skydiving adventure. Jump from 15,000 feet and enjoy breathtaking views of Cox's Bazar coastline.",
    highlights: [
      "Professional certified instructors",
      "Safety briefing and training included",
      "High-quality equipment provided",
      "Certificate of completion",
      "Photos and videos available",
      "Transport from hotel included"
    ],
    included: [
      "Professional instructor",
      "Safety equipment",
      "Training session",
      "Certificate",
      "Hotel pickup & drop"
    ],
    notIncluded: [
      "Personal expenses",
      "Photos & videos (optional)",
      "Insurance",
      "Meals"
    ],
    requirements: [
      "Minimum age: 18 years",
      "Maximum weight: 100kg",
      "Good physical health",
      "No heart conditions",
      "No recent surgeries"
    ],
    schedule: [
      { time: "08:00 AM", activity: "Hotel pickup" },
      { time: "09:00 AM", activity: "Arrival at jump site" },
      { time: "09:30 AM", activity: "Safety briefing & training" },
      { time: "10:30 AM", activity: "Equipment check" },
      { time: "11:00 AM", activity: "Skydiving jump" },
      { time: "12:00 PM", activity: "Return to hotel" }
    ],
    provider: {
      name: "Adventure Seekers BD",
      rating: 4.9,
      experience: "8 years",
      certified: true,
      phone: "+880 1711-123456"
    }
  };

  const tabs = [
    { id: 'overview', name: 'Overview' },
    { id: 'itinerary', name: 'Itinerary' },
    { id: 'reviews', name: 'Reviews' },
    { id: 'provider', name: 'Provider' }
  ];

  const reviews = [
    {
      id: 1,
      name: "Sarah Ahmed",
      rating: 5,
      date: "2 days ago",
      comment: "Absolutely amazing experience! The instructors were professional and made me feel safe throughout. The views were breathtaking!",
      helpful: 12
    },
    {
      id: 2,
      name: "John Smith",
      rating: 4,
      date: "1 week ago",
      comment: "Great adventure! A bit scary at first but totally worth it. The team was very supportive.",
      helpful: 8
    },
    {
      id: 3,
      name: "Rashida Khan",
      rating: 5,
      date: "2 weeks ago",
      comment: "Best experience of my life! Highly recommend to anyone looking for an adrenaline rush.",
      helpful: 15
    }
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header with image */}
      <div className="relative">
        <div className="h-80 bg-gradient-to-br from-primary-400 to-adventure-400 flex items-center justify-center">
          <div className="text-center text-white">
            <div className="text-8xl mb-4">🪂</div>
            <p className="text-lg opacity-80">Professional Skydiving</p>
          </div>
        </div>
        
        {/* Header controls */}
        <div className="absolute top-6 left-6 right-6 flex items-center justify-between">
          <button 
            onClick={() => navigate(-1)}
            className="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white"
          >
            <ArrowLeft className="w-6 h-6" />
          </button>
          <div className="flex items-center space-x-3">
            <button className="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white">
              <Share className="w-5 h-5" />
            </button>
            <button 
              onClick={() => setIsFavorite(!isFavorite)}
              className="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white"
            >
              <Heart className={`w-5 h-5 ${isFavorite ? 'fill-current text-red-400' : ''}`} />
            </button>
          </div>
        </div>

        {/* Discount badge */}
        {activity.discount && (
          <div className="absolute top-20 left-6 bg-red-500 text-white px-3 py-1 rounded-lg text-sm font-medium">
            {activity.discount}% OFF
          </div>
        )}
      </div>

      {/* Content */}
      <div className="bg-white rounded-t-3xl -mt-6 relative z-10 pt-6">
        <div className="px-6">
          {/* Title and rating */}
          <div className="mb-6">
            <div className="flex items-start justify-between mb-2">
              <div className="flex-1">
                <h1 className="text-2xl font-display font-bold text-gray-900 mb-2">{activity.title}</h1>
                <div className="flex items-center text-gray-500 text-sm mb-3">
                  <MapPin className="w-4 h-4 mr-1" />
                  <span>{activity.location}</span>
                </div>
              </div>
              <div className="text-right">
                <div className="flex items-center justify-end text-yellow-500 mb-1">
                  <Star className="w-5 h-5 fill-current" />
                  <span className="ml-1 font-semibold text-gray-900">{activity.rating}</span>
                  <span className="text-sm text-gray-500 ml-1">({activity.reviews} reviews)</span>
                </div>
                <span className="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded-full">
                  {activity.category}
                </span>
              </div>
            </div>

            {/* Key info */}
            <div className="flex items-center justify-between py-4 border-t border-b border-gray-100">
              <div className="flex items-center text-sm text-gray-600">
                <Clock className="w-4 h-4 mr-2" />
                <span>{activity.duration}</span>
              </div>
              <div className="flex items-center text-sm text-gray-600">
                <Users className="w-4 h-4 mr-2" />
                <span>{activity.groupSize}</span>
              </div>
              <div className="flex items-center text-sm text-gray-600">
                <Shield className="w-4 h-4 mr-2" />
                <span>{activity.difficulty}</span>
              </div>
            </div>
          </div>

          {/* Price */}
          <div className="mb-6">
            <div className="flex items-center space-x-3">
              <span className="text-3xl font-bold text-gray-900">৳{activity.price.toLocaleString()}</span>
              {activity.originalPrice && (
                <span className="text-lg text-gray-500 line-through">৳{activity.originalPrice.toLocaleString()}</span>
              )}
              <span className="text-sm text-gray-500">per person</span>
            </div>
          </div>

          {/* Tabs */}
          <div className="mb-6">
            <div className="flex space-x-1 bg-gray-100 rounded-xl p-1">
              {tabs.map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  className={`flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 ${
                    activeTab === tab.id
                      ? 'bg-white text-primary-600 shadow-sm'
                      : 'text-gray-600 hover:text-gray-900'
                  }`}
                >
                  {tab.name}
                </button>
              ))}
            </div>
          </div>

          {/* Tab Content */}
          <div className="mb-8">
            {activeTab === 'overview' && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="space-y-6"
              >
                <div>
                  <h3 className="font-semibold text-gray-900 mb-3">Description</h3>
                  <p className="text-gray-600 leading-relaxed">{activity.description}</p>
                </div>

                <div>
                  <h3 className="font-semibold text-gray-900 mb-3">Highlights</h3>
                  <div className="space-y-2">
                    {activity.highlights.map((highlight, index) => (
                      <div key={index} className="flex items-center space-x-3">
                        <CheckCircle className="w-5 h-5 text-green-500 flex-shrink-0" />
                        <span className="text-gray-600">{highlight}</span>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <h3 className="font-semibold text-gray-900 mb-3">What's Included</h3>
                    <div className="space-y-2">
                      {activity.included.map((item, index) => (
                        <div key={index} className="flex items-center space-x-3">
                          <CheckCircle className="w-4 h-4 text-green-500 flex-shrink-0" />
                          <span className="text-sm text-gray-600">{item}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                  <div>
                    <h3 className="font-semibold text-gray-900 mb-3">Not Included</h3>
                    <div className="space-y-2">
                      {activity.notIncluded.map((item, index) => (
                        <div key={index} className="flex items-center space-x-3">
                          <div className="w-4 h-4 border-2 border-gray-300 rounded-full flex-shrink-0"></div>
                          <span className="text-sm text-gray-600">{item}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>

                <div>
                  <h3 className="font-semibold text-gray-900 mb-3">Requirements</h3>
                  <div className="space-y-2">
                    {activity.requirements.map((requirement, index) => (
                      <div key={index} className="flex items-center space-x-3">
                        <AlertTriangle className="w-4 h-4 text-orange-500 flex-shrink-0" />
                        <span className="text-sm text-gray-600">{requirement}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </motion.div>
            )}

            {activeTab === 'itinerary' && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="space-y-4"
              >
                <h3 className="font-semibold text-gray-900 mb-4">Daily Schedule</h3>
                {activity.schedule.map((item, index) => (
                  <div key={index} className="flex items-start space-x-4 pb-4 border-b border-gray-100 last:border-b-0">
                    <div className="w-20 text-sm font-medium text-primary-600 flex-shrink-0">
                      {item.time}
                    </div>
                    <div className="flex-1">
                      <p className="text-gray-900">{item.activity}</p>
                    </div>
                  </div>
                ))}
              </motion.div>
            )}

            {activeTab === 'reviews' && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="space-y-6"
              >
                <div className="flex items-center justify-between">
                  <h3 className="font-semibold text-gray-900">Reviews ({activity.reviews})</h3>
                  <button className="text-primary-600 text-sm font-medium">Write a review</button>
                </div>
                <div className="space-y-4">
                  {reviews.map((review) => (
                    <div key={review.id} className="p-4 bg-gray-50 rounded-xl">
                      <div className="flex items-start justify-between mb-2">
                        <div>
                          <h4 className="font-medium text-gray-900">{review.name}</h4>
                          <div className="flex items-center space-x-2 mt-1">
                            <div className="flex items-center text-yellow-500">
                              {[...Array(5)].map((_, i) => (
                                <Star key={i} className={`w-4 h-4 ${i < review.rating ? 'fill-current' : 'text-gray-300'}`} />
                              ))}
                            </div>
                            <span className="text-sm text-gray-500">{review.date}</span>
                          </div>
                        </div>
                      </div>
                      <p className="text-gray-600 mb-3">{review.comment}</p>
                      <button className="text-sm text-gray-500">
                        Helpful ({review.helpful})
                      </button>
                    </div>
                  ))}
                </div>
              </motion.div>
            )}

            {activeTab === 'provider' && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="space-y-6"
              >
                <div className="flex items-start space-x-4">
                  <div className="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center">
                    <Camera className="w-8 h-8 text-primary-600" />
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center space-x-2 mb-1">
                      <h3 className="font-semibold text-gray-900">{activity.provider.name}</h3>
                      {activity.provider.certified && (
                        <CheckCircle className="w-5 h-5 text-green-500" />
                      )}
                    </div>
                    <div className="flex items-center text-yellow-500 mb-2">
                      <Star className="w-4 h-4 fill-current" />
                      <span className="ml-1 text-sm font-medium text-gray-900">{activity.provider.rating}</span>
                      <span className="text-sm text-gray-500 ml-1">• {activity.provider.experience} experience</span>
                    </div>
                    <div className="flex items-center space-x-4">
                      <button className="flex items-center space-x-2 px-4 py-2 bg-primary-600 text-white rounded-lg">
                        <Phone className="w-4 h-4" />
                        <span>Call</span>
                      </button>
                      <button className="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg">
                        <MessageCircle className="w-4 h-4" />
                        <span>Message</span>
                      </button>
                    </div>
                  </div>
                </div>
              </motion.div>
            )}
          </div>
        </div>

        {/* Fixed bottom booking bar */}
        <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-4 z-20">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Starting from</p>
              <p className="text-xl font-bold text-gray-900">৳{activity.price.toLocaleString()}</p>
            </div>
            <div className="flex items-center space-x-3">
              <button 
                onClick={() => navigate(`/booking/${activity.id}`)}
                className="btn-primary flex items-center space-x-2"
              >
                <Calendar className="w-5 h-5" />
                <span>Book Now</span>
              </button>
            </div>
          </div>
        </div>

        {/* Bottom padding for fixed booking bar */}
        <div className="h-20"></div>
      </div>
    </div>
  );
};

export default ActivityDetailScreen;