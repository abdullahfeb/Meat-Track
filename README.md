# CollabAI - Collaborative AI Chat Platform

A secure, real-time collaborative AI chat platform inspired by Google Docs functionality. Multiple users can work together in shared conversation spaces with AI models, enabling teams to brainstorm, research, and generate content more effectively.

## 🚀 Features

### Core Functionality
- **Real-time Collaboration**: Multiple users can participate in shared AI chat sessions
- **Role-based Access Control**: Assign Editor (can interact with AI) or Viewer (read-only) roles
- **AI Integration**: Support for multiple AI models (GPT-3.5, GPT-4, Claude-3)
- **Live Presence Indicators**: See who's online and typing in real-time
- **Session Management**: Create, join, and manage chat sessions with security controls

### Security & Access Control
- **Optional Security Codes**: Protect sessions with access codes
- **Session Expiry**: Set time limits for session access
- **User Authentication**: Secure login/registration with session management
- **Activity Logging**: Detailed audit trails for transparency and accountability

### Collaboration Features
- **Real-time Chat Synchronization**: Instant message updates across all participants
- **Typing Indicators**: See when team members are composing messages
- **Participant Management**: Invite team members by email with role assignment
- **Session Statistics**: View participant count, message count, and activity metrics

### Export & Reporting
- **Multiple Export Formats**: Export sessions to PDF, Markdown, or Word documents
- **Activity History**: Complete logs of who asked what and when AI responded
- **Session Analytics**: Track participation and engagement metrics

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+ (via XAMPP)
- **Server**: Apache (XAMPP)
- **Styling**: Custom CSS with Font Awesome icons
- **Real-time Updates**: HTTP polling (can be upgraded to WebSockets)

## 📋 Prerequisites

- **XAMPP** (Apache + MySQL + PHP)
- **Web Browser** (Chrome, Firefox, Safari, or Edge)
- **Email Server** (optional, for invitation emails)

## 🔧 Installation & Setup

### 1. Install XAMPP
1. Download and install [XAMPP](https://www.apachefriends.org/download.html)
2. Start Apache and MySQL services in XAMPP Control Panel

### 2. Setup Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Import the database schema:
   ```sql
   -- Run the contents of database/schema.sql
   ```
3. Or manually create the database by running:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

### 3. Configure Application
1. Copy all project files to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\collabai\  (Windows)
   /Applications/XAMPP/htdocs/collabai/  (macOS)
   /opt/lampp/htdocs/collabai/  (Linux)
   ```

2. Update database configuration in `config/database.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', ''); // Default XAMPP password is empty
   define('DB_NAME', 'collabai');
   ```

### 4. Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/collabai`
3. Register a new account or use the default admin account:
   - **Email**: admin@collabai.com
   - **Password**: admin123

## 🎯 Usage Guide

### Getting Started
1. **Register an Account**: Create your user profile with email verification
2. **Login**: Access your dashboard with your credentials
3. **Create a Session**: Set up a new AI collaboration session
4. **Invite Team Members**: Send email invitations with role assignments
5. **Start Collaborating**: Begin your AI-powered discussion

### Creating a Session
1. Click "Create New Session" from your dashboard
2. Fill in session details:
   - **Title**: Descriptive name for your session
   - **Description**: Brief overview of the session purpose
   - **AI Model**: Choose your preferred AI assistant
   - **Max Participants**: Set the maximum number of team members
   - **Access Code**: Optional security code for entry
   - **Expiry Date**: Optional time limit for the session

### Managing Participants
- **Owner**: Full control over the session (create, invite, manage)
- **Editor**: Can send messages and interact with AI
- **Viewer**: Read-only access to observe the conversation

### Real-time Features
- **Live Messaging**: Messages appear instantly for all participants
- **Typing Indicators**: See when someone is composing a message
- **Presence Status**: Green indicators show who's currently online
- **Auto-refresh**: Content updates every 2 seconds automatically

## 🔒 Security Features

### Authentication
- Secure password hashing with PHP's `password_hash()`
- Session tokens with configurable expiration
- "Remember me" functionality with secure cookies
- Email verification for new accounts

### Access Control
- Role-based permissions (Owner/Editor/Viewer)
- Optional session access codes
- Time-based session expiry
- IP address and user agent logging

### Activity Logging
All actions are logged for audit purposes:
- User registration and login attempts
- Session creation and modifications
- Message sending and AI responses
- Permission changes and invitations
- Failed access attempts

## 📊 Database Schema

### Key Tables
- **users**: User accounts and profiles
- **chat_sessions**: Session metadata and settings
- **session_participants**: User roles and permissions
- **messages**: Chat messages and AI responses
- **activity_log**: Comprehensive audit trail
- **user_presence**: Real-time presence and typing status

### Relationships
- Sessions belong to owners (users)
- Participants have many-to-many relationships with sessions
- Messages belong to sessions and users
- Activity logs track all session-related actions

## 🔄 Real-time Updates

The application uses HTTP polling for real-time features:
- **Message Polling**: Checks for new messages every 2 seconds
- **Presence Updates**: Updates online status and typing indicators
- **Participant Sync**: Refreshes participant list and status

### Upgrading to WebSockets
For production use, consider upgrading to WebSockets:
1. Implement WebSocket server (Node.js with Socket.io or PHP ReactPHP)
2. Replace polling with WebSocket connections
3. Add WebSocket authentication and session management

## 🤖 AI Integration

### Current Implementation
- **Demo Mode**: Simulated AI responses for testing
- **Multiple Models**: Support for GPT-3.5, GPT-4, Claude-3
- **Response Metadata**: Tracks model used and response timing

### Production Integration
To integrate with real AI services:

1. **OpenAI API**:
   ```php
   // In api/messages/send.php, replace generateAIResponse() with:
   $client = new OpenAI\Client('your-api-key');
   $response = $client->chat()->create([
       'model' => 'gpt-3.5-turbo',
       'messages' => $messages
   ]);
   ```

2. **Anthropic Claude**:
   ```php
   // Integrate with Anthropic's API
   $response = $anthropic->messages()->create([
       'model' => 'claude-3-sonnet',
       'messages' => $messages
   ]);
   ```

## 📈 Performance Optimization

### Database Optimization
- Indexed frequently queried columns
- Database views for complex queries
- Query optimization for message loading
- Efficient presence tracking

### Frontend Optimization
- Minimal JavaScript dependencies
- Optimized CSS with utility classes
- Image optimization and caching
- Progressive enhancement

### Scaling Considerations
- Database connection pooling
- Redis for session storage
- Load balancing for multiple servers
- CDN for static assets

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check XAMPP MySQL service is running
   - Verify database credentials in `config/database.php`
   - Ensure `collabai` database exists

2. **Session Not Loading**
   - Check browser console for JavaScript errors
   - Verify user has proper session permissions
   - Check if session has expired

3. **Messages Not Updating**
   - Confirm real-time polling is working
   - Check network connectivity
   - Verify API endpoints are accessible

4. **Invitation Emails Not Sending**
   - Configure PHP mail settings
   - Check email server configuration
   - Verify SMTP settings

### Debug Mode
Enable error reporting for development:
```php
// Add to config/database.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 🚀 Deployment

### Production Deployment
1. **Server Requirements**:
   - PHP 7.4+ with PDO MySQL extension
   - MySQL 8.0+ or MariaDB 10.4+
   - Apache or Nginx web server
   - SSL certificate for HTTPS

2. **Security Hardening**:
   - Change default database passwords
   - Enable HTTPS only
   - Configure proper file permissions
   - Set up regular database backups

3. **Environment Configuration**:
   - Update database credentials
   - Configure email server settings
   - Set up proper error logging
   - Enable production optimizations

## 🤝 Contributing

This is a demonstration project showcasing collaborative AI chat functionality. For production use, consider:

- Adding comprehensive error handling
- Implementing proper email sending
- Adding user management features
- Enhancing security measures
- Adding more AI model integrations

## 📄 License

This project is created for educational and demonstration purposes. Feel free to use and modify as needed for your projects.

## 🆘 Support

For issues or questions:
1. Check the troubleshooting section
2. Review the browser console for errors
3. Check PHP error logs in XAMPP
4. Verify database connectivity and structure

---

**CollabAI** - Bringing teams and AI together for collaborative innovation.