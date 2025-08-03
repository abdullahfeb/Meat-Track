-- CollabAI Database Schema
-- Created for XAMPP/MySQL

CREATE DATABASE IF NOT EXISTS collabai;
USE collabai;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(255) DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Chat sessions table
CREATE TABLE chat_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(36) UNIQUE NOT NULL, -- UUID
    title VARCHAR(200) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    access_code VARCHAR(20) DEFAULT NULL,
    expires_at TIMESTAMP NULL,
    max_participants INT DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    ai_model VARCHAR(50) DEFAULT 'gpt-3.5-turbo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_owner_id (owner_id),
    INDEX idx_expires_at (expires_at)
);

-- Session participants table (many-to-many relationship)
CREATE TABLE session_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(36) NOT NULL,
    user_id INT NOT NULL,
    role ENUM('owner', 'editor', 'viewer') NOT NULL DEFAULT 'viewer',
    invited_by INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_online BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_session_user (session_id, user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id)
);

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(36) NOT NULL,
    user_id INT NULL, -- NULL for AI messages
    message_type ENUM('user', 'ai', 'system') NOT NULL DEFAULT 'user',
    content TEXT NOT NULL,
    metadata JSON DEFAULT NULL, -- For storing AI response metadata, tokens used, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    edited_at TIMESTAMP NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Session invitations table
CREATE TABLE session_invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(36) NOT NULL,
    email VARCHAR(100) NOT NULL,
    invited_by INT NOT NULL,
    role ENUM('editor', 'viewer') NOT NULL DEFAULT 'viewer',
    invitation_token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_accepted BOOLEAN DEFAULT FALSE,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_email (email),
    INDEX idx_token (invitation_token),
    INDEX idx_expires_at (expires_at)
);

-- Activity log table for audit trail
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(36) NOT NULL,
    user_id INT NULL,
    action_type VARCHAR(50) NOT NULL, -- 'message_sent', 'user_joined', 'role_changed', 'session_created', etc.
    action_details JSON DEFAULT NULL, -- Store additional context as JSON
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- User sessions for authentication
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(128) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- Email verification tokens
CREATE TABLE email_verification_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- Export history table
CREATE TABLE export_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(36) NOT NULL,
    user_id INT NOT NULL,
    export_type ENUM('pdf', 'markdown', 'docx') NOT NULL,
    file_path VARCHAR(500) NULL,
    export_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Real-time presence tracking
CREATE TABLE user_presence (
    user_id INT PRIMARY KEY,
    session_id VARCHAR(36) NULL,
    is_online BOOLEAN DEFAULT FALSE,
    is_typing BOOLEAN DEFAULT FALSE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    typing_started_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_last_activity (last_activity)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, is_verified) VALUES 
('admin', 'admin@collabai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', TRUE);

-- Create indexes for better performance
CREATE INDEX idx_messages_session_created ON messages(session_id, created_at);
CREATE INDEX idx_activity_session_created ON activity_log(session_id, created_at);
CREATE INDEX idx_participants_session_role ON session_participants(session_id, role);

-- Create views for common queries
CREATE VIEW active_sessions AS
SELECT 
    s.*,
    u.username as owner_username,
    u.full_name as owner_name,
    COUNT(sp.user_id) as participant_count,
    COUNT(CASE WHEN sp.is_online = TRUE THEN 1 END) as online_count
FROM chat_sessions s
LEFT JOIN users u ON s.owner_id = u.id
LEFT JOIN session_participants sp ON s.session_id = sp.session_id
WHERE s.is_active = TRUE 
    AND (s.expires_at IS NULL OR s.expires_at > NOW())
GROUP BY s.id;

CREATE VIEW session_stats AS
SELECT 
    s.session_id,
    s.title,
    COUNT(m.id) as message_count,
    COUNT(DISTINCT m.user_id) as unique_contributors,
    MAX(m.created_at) as last_message_at,
    COUNT(sp.user_id) as total_participants
FROM chat_sessions s
LEFT JOIN messages m ON s.session_id = m.session_id AND m.is_deleted = FALSE
LEFT JOIN session_participants sp ON s.session_id = sp.session_id
GROUP BY s.session_id;