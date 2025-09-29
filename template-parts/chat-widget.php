<?php
/**
 * Professional Chat Widget
 * Modern chatbot interface for ForexDrift
 */
?>
<div id="whisp-chat-widget" class="chat-widget">
  <!-- Chat Button -->
  <button class="chat-toggle-btn" id="chatToggleBtn" aria-label="Open Chat">
    <div class="chat-btn-content">
      <i class="fas fa-comments chat-btn-icon"></i>
      <i class="fas fa-times chat-btn-close"></i>
    </div>
    <div class="chat-btn-notification" id="chatNotification">
      <span class="notification-count">1</span>
    </div>
  </button>

  <!-- Chat Window -->
  <div class="chat-window" id="chatWindow">
    <!-- Chat Header -->
    <div class="chat-header">
      <div class="chat-agent-info">
        <div class="agent-avatar">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/chat-agent.jpg" alt="Support Agent" class="agent-image">
          <div class="agent-status online"></div>
        </div>
        <div class="agent-details">
          <h4 class="agent-name">Support Chat</h4>
          <p class="agent-status-text">Online • Typically replies in minutes</p>
        </div>
      </div>
      <div class="chat-actions">
        <button class="chat-minimize" id="chatMinimize" aria-label="Minimize Chat">
          <i class="fas fa-minus"></i>
        </button>
        <button class="chat-close" id="chatClose" aria-label="Close Chat">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages" id="chatMessages">
      <!-- Lead Capture Form -->
      <div class="lead-capture-form" id="leadCaptureForm">
        <div class="form-welcome">
          <div class="welcome-avatar">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/chat-agent.jpg" alt="Support" class="welcome-image">
          </div>
          <div class="welcome-content">
<h3>Welcome! 👋</h3>
<p>I'm here to help! Please share your details so we can provide personalized assistance.</p>
          </div>
        </div>

        <form class="contact-form" id="contactDetailsForm">
          <div class="form-step active" data-step="1">
            <div class="step-header">
              <h4><i class="fas fa-user"></i> Your Name</h4>
              <span class="step-counter">Step 1 of 4</span>
            </div>
            <div class="form-group">
              <input type="text" id="userName" name="name" placeholder="Enter your full name" required>
              <div class="input-validation"></div>
            </div>
            <button type="button" class="form-next-btn" data-next="2">
              Next <i class="fas fa-arrow-right"></i>
            </button>
          </div>

          <div class="form-step" data-step="2">
            <div class="step-header">
              <h4><i class="fas fa-envelope"></i> Email Address</h4>
              <span class="step-counter">Step 2 of 4</span>
            </div>
            <div class="form-group">
              <input type="email" id="userEmail" name="email" placeholder="Enter your email address" required>
              <div class="input-validation"></div>
            </div>
            <div class="form-navigation">
              <button type="button" class="form-back-btn" data-back="1">
                <i class="fas fa-arrow-left"></i> Back
              </button>
              <button type="button" class="form-next-btn" data-next="3">
                Next <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>

          <div class="form-step" data-step="3">
            <div class="step-header">
              <h4><i class="fas fa-phone"></i> Phone Number</h4>
              <span class="step-counter">Step 3 of 4</span>
            </div>
            <div class="form-group">
              <input type="tel" id="userPhone" name="phone" placeholder="Enter your phone number" required>
              <div class="input-validation"></div>
            </div>
            <div class="form-navigation">
              <button type="button" class="form-back-btn" data-back="2">
                <i class="fas fa-arrow-left"></i> Back
              </button>
              <button type="button" class="form-next-btn" data-next="4">
                Next <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>

          <div class="form-step" data-step="4">
            <div class="step-header">
              <h4><i class="fas fa-globe"></i> Country</h4>
              <span class="step-counter">Step 4 of 4</span>
            </div>
            <div class="form-group">
              <select id="userCountry" name="country" required>
                <option value="">Select your country</option>
                <option value="United States">United States</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="Canada">Canada</option>
                <option value="Australia">Australia</option>
                <option value="Germany">Germany</option>
                <option value="France">France</option>
                <option value="Italy">Italy</option>
                <option value="Spain">Spain</option>
                <option value="Netherlands">Netherlands</option>
                <option value="Switzerland">Switzerland</option>
                <option value="Japan">Japan</option>
                <option value="Singapore">Singapore</option>
                <option value="Hong Kong">Hong Kong</option>
                <option value="UAE">United Arab Emirates</option>
                <option value="Other">Other</option>
              </select>
              <div class="input-validation"></div>
            </div>
            <div class="form-navigation">
              <button type="button" class="form-back-btn" data-back="3">
                <i class="fas fa-arrow-left"></i> Back
              </button>
              <button type="submit" class="form-submit-btn" id="submitContactForm">
                <i class="fas fa-check"></i> Start Chat
              </button>
            </div>
          </div>

          <div class="form-progress">
            <div class="progress-bar">
              <div class="progress-fill" style="width: 25%;"></div>
            </div>
            <div class="progress-dots">
              <span class="dot active"></span>
              <span class="dot"></span>
              <span class="dot"></span>
              <span class="dot"></span>
            </div>
          </div>
        </form>
      </div>

      <!-- Chat Messages Area (Hidden Initially) -->
      <div class="chat-conversation" id="chatConversation" style="display: none;">
        <div class="chat-welcome">
          <div class="welcome-avatar">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/chat-agent.jpg" alt="Support" class="welcome-image">
          </div>
          <div class="welcome-content">
<h3>Thanks <span id="chatUserName">there</span>! 👋</h3>
            <p>Now I can provide personalized assistance. How can I help you today?</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <h4>Quick Help:</h4>
          <div class="quick-buttons">
            <button class="quick-btn" data-message="Tell me about PAMM accounts">
              <i class="fas fa-chart-line"></i>
              PAMM Accounts
            </button>
            <button class="quick-btn" data-message="What are the capital requirements?">
              <i class="fas fa-dollar-sign"></i>
              Capital Requirements
            </button>
            <button class="quick-btn" data-message="How does profit sharing work?">
              <i class="fas fa-percentage"></i>
              Profit Sharing
            </button>
            <button class="quick-btn" data-message="I want to become a partner">
              <i class="fas fa-handshake"></i>
              Become Partner
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Input -->
    <div class="chat-input-area">
      <div class="chat-input-container">
        <input 
          type="text" 
          class="chat-input" 
          id="chatInput" 
          placeholder="Type your message..."
          maxlength="500"
        >
        <button class="chat-send-btn" id="chatSendBtn" disabled>
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
      <div class="chat-footer">
        <small>
          <i class="fas fa-shield-alt"></i>
          Secure & Confidential
        </small>
      </div>
    </div>

    <!-- Typing Indicator -->
    <div class="typing-indicator" id="typingIndicator">
      <div class="typing-avatar">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/chat-agent.jpg" alt="Agent">
      </div>
      <div class="typing-dots">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
    </div>
  </div>

  <!-- Chat Overlay (for mobile) -->
  <div class="chat-overlay" id="chatOverlay"></div>
</div>

<!-- Floating Chat Notification -->
<div class="chat-floating-notification" id="chatFloatingNotification">
  <div class="notification-content">
    <div class="notification-avatar">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/chat-agent.jpg" alt="Support">
    </div>
    <div class="notification-text">
      <strong>Need help getting started?</strong>
      <span>Chat with our partnership experts!</span>
    </div>
    <button class="notification-close" onclick="this.parentElement.parentElement.style.display='none'">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>