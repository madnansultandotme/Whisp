// Professional Chat Widget for Whisp
class WhispChatWidget {
  constructor() {
    this.isOpen = false;
    this.isMinimized = false;
    this.messages = [];
    this.typingTimeout = null;
    this.leadId = null;
    this.formCompleted = false;
    this.currentStep = 1;
    this.init();
  }

  init() {
    console.log('Initializing Whisp Chat Widget...');
    this.bindEvents();
    this.initializeForm();
    this.showWelcomeNotification();
    // Only load chat history if form was previously completed
    this.checkFormCompletionStatus();
  }

  bindEvents() {
    const toggleBtn = document.getElementById('chatToggleBtn');
    const closeBtn = document.getElementById('chatClose');
    const minimizeBtn = document.getElementById('chatMinimize');
    const sendBtn = document.getElementById('chatSendBtn');
    const chatInput = document.getElementById('chatInput');
    const overlay = document.getElementById('chatOverlay');
    const quickButtons = document.querySelectorAll('.quick-btn');

    // Toggle chat window
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => this.toggleChat());
    }

    // Close chat
    if (closeBtn) {
      closeBtn.addEventListener('click', () => this.closeChat());
    }

    // Minimize chat
    if (minimizeBtn) {
      minimizeBtn.addEventListener('click', () => this.minimizeChat());
    }

    // Send message
    if (sendBtn) {
      sendBtn.addEventListener('click', () => this.sendMessage());
    }

    // Input handling
    if (chatInput) {
      chatInput.addEventListener('input', () => this.handleInput());
      chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          this.sendMessage();
        }
      });
    }

    // Overlay click to close
    if (overlay) {
      overlay.addEventListener('click', () => this.closeChat());
    }

    // Quick action buttons
    quickButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const message = btn.getAttribute('data-message');
        if (message) {
          this.sendQuickMessage(message);
        }
      });
    });

    // Form navigation events
    this.bindFormEvents();
  }

  toggleChat() {
    if (this.isOpen) {
      this.closeChat();
    } else {
      this.openChat();
    }
  }

  openChat() {
    const chatWindow = document.getElementById('chatWindow');
    const toggleBtn = document.getElementById('chatToggleBtn');
    const overlay = document.getElementById('chatOverlay');
    const notification = document.getElementById('chatNotification');

    if (chatWindow && toggleBtn) {
      this.isOpen = true;
      this.isMinimized = false;
      
      chatWindow.classList.add('open');
      chatWindow.classList.remove('minimized');
      toggleBtn.classList.add('active');
      
      if (overlay) {
        overlay.classList.add('show');
      }

      // Hide notification badge
      if (notification) {
        notification.classList.remove('show');
      }

      // Focus on input
      setTimeout(() => {
        const input = document.getElementById('chatInput');
        if (input) input.focus();
      }, 300);

      // Hide floating notification
      this.hideFloatingNotification();
    }
  }

  closeChat() {
    const chatWindow = document.getElementById('chatWindow');
    const toggleBtn = document.getElementById('chatToggleBtn');
    const overlay = document.getElementById('chatOverlay');

    if (chatWindow && toggleBtn) {
      this.isOpen = false;
      this.isMinimized = false;
      
      chatWindow.classList.remove('open', 'minimized');
      toggleBtn.classList.remove('active');
      
      if (overlay) {
        overlay.classList.remove('show');
      }
    }
  }

  minimizeChat() {
    const chatWindow = document.getElementById('chatWindow');
    
    if (chatWindow && this.isOpen) {
      this.isMinimized = !this.isMinimized;
      chatWindow.classList.toggle('minimized', this.isMinimized);
    }
  }

  handleInput() {
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatSendBtn');
    
    if (input && sendBtn) {
      const hasText = input.value.trim().length > 0;
      sendBtn.disabled = !hasText;
    }
  }

  sendMessage() {
    const input = document.getElementById('chatInput');
    if (!input || !input.value.trim()) return;

    const message = input.value.trim();
    input.value = '';
    this.handleInput(); // Update send button state

    // Add user message
    this.addMessage(message, 'user');

    // Show typing indicator
    this.showTypingIndicator();

    // Send message to backend if we have a lead ID
    if (this.leadId && this.formCompleted) {
      this.sendMessageToBackend(message);
    } else {
      // Generate bot response for demo
      setTimeout(() => {
        this.hideTypingIndicator();
        const response = this.generateBotResponse(message);
        this.addMessage(response, 'bot');
      }, 1000 + Math.random() * 2000);
    }
  }

  sendQuickMessage(message) {
    // Add user message
    this.addMessage(message, 'user');

    // Show typing indicator
    this.showTypingIndicator();

    // Generate bot response
    setTimeout(() => {
      this.hideTypingIndicator();
      const response = this.generateBotResponse(message);
      this.addMessage(response, 'bot');
    }, 1500);
  }

  addMessage(text, sender) {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;

    const messageData = {
      text,
      sender,
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };

    this.messages.push(messageData);

    // Remove quick actions if this is the first user message
    if (sender === 'user' && this.messages.filter(m => m.sender === 'user').length === 1) {
      const quickActions = messagesContainer.querySelector('.quick-actions');
      if (quickActions) {
        quickActions.style.opacity = '0.5';
        quickActions.style.pointerEvents = 'none';
      }
    }

    // Create message element
    const messageElement = document.createElement('div');
    messageElement.className = `message ${sender}`;
    messageElement.innerHTML = `
      ${sender === 'bot' ? `
        <div class="message-avatar">
          <img src="${this.getAgentImageUrl()}" alt="Agent">
        </div>
      ` : ''}
      <div class="message-wrapper">
        <div class="message-content">${this.formatMessage(text)}</div>
        <div class="message-time">${messageData.timestamp}</div>
      </div>
      ${sender === 'user' ? `
        <div class="message-avatar">
          <div style="width: 32px; height: 32px; background: var(--brand); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #000; font-weight: bold; font-size: 14px;">
            U
          </div>
        </div>
      ` : ''}
    `;

    messagesContainer.appendChild(messageElement);
    this.scrollToBottom();
    this.saveChatHistory();
  }

  formatMessage(text) {
    // Simple formatting for links and emphasis
    return text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
  }

  generateBotResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
    // Partnership-related responses
    if (message.includes('pamm') || message.includes('tell me about pamm')) {
      return `**PAMM Accounts** are perfect for new partners! 📈\n\n• **Minimum**: $5,000-$10,000\n• **Professional management** with transparent profit sharing\n• **Regulated brokers** (CySEC, FCA, ASIC)\n• **Success fee**: 20-30% of profits only\n\nWould you like to know more about getting started?`;
    }
    
    if (message.includes('capital') || message.includes('minimum') || message.includes('requirement')) {
      return `**Capital Requirements** by partnership model:\n\n💰 **PAMM Accounts**: $5,000-$10,000\n💼 **MAM/Copy Trading**: $10,000-$25,000\n🏢 **Pool Account Management**: $25,000+\n\nWe can customize requirements based on your goals. Would you like a consultation?`;
    }
    
    if (message.includes('profit') || message.includes('sharing') || message.includes('how does profit')) {
      return `**Profit Sharing** is completely transparent:\n\n✅ **Success Fee**: 20-30% of net profits only\n✅ **High Water Mark**: Fees only on new profit highs\n✅ **No Management Fees**: We earn when you profit\n✅ **Real-time Access**: 24/7 performance monitoring\n\n**Example**: $1,000 profit = You keep $750-800, we earn $200-250`;
    }
    
    if (message.includes('partner') || message.includes('become') || message.includes('join')) {
      return `Great choice! 🤝 **Becoming a Partner** is easy:\n\n1️⃣ **Choose your model** (PAMM/MAM/Pool)\n2️⃣ **Initial consultation** with our team\n3️⃣ **KYC verification** process\n4️⃣ **Fund your account** & start growing\n\n**Ready to start?** I can connect you with our partnership specialist right now!`;
    }
    
    if (message.includes('legal') || message.includes('regulated') || message.includes('license')) {
      return `**100% Legal & Regulated** ⚖️\n\n• **EU MiFID II** compliant\n• **US CFTC** guidelines adherence\n• **CySEC, FCA, ASIC** licensed brokers\n• **Segregated accounts** in Tier-1 banks\n• **Insurance coverage** up to regulatory limits\n\nYour funds are completely secure!`;
    }
    
    if (message.includes('risk') || message.includes('safe') || message.includes('security')) {
      return `**Maximum Security** is our priority 🛡️\n\n🏦 **Segregated Accounts** in top-tier banks\n🔒 **Bank-grade SSL** encryption\n📱 **2FA Security** for all accounts\n💼 **Regulatory Protection** (FSCS, ICF)\n⚖️ **Legal Framework** compliance\n\nYour investment is protected at every level!`;
    }
    
    if (message.includes('hello') || message.includes('hi') || message.includes('hey')) {
      return `Hello! 👋 Welcome to ForexDrift!\n\nI'm here to help you learn about our partnership opportunities. Whether you're interested in PAMM accounts, MAM trading, or pool management - I can guide you through everything!\n\n**What would you like to know first?**`;
    }
    
    if (message.includes('contact') || message.includes('speak') || message.includes('human')) {
      return `I'd be happy to connect you with our team! 🤝\n\n**Contact Options:**\n📧 Email: support@forexdrift.com\n💬 Telegram: @forexdrift_support\n📞 Schedule a call with our partnership specialist\n\nWould you like me to arrange a **free consultation** for you?`;
    }
    
    if (message.includes('time') || message.includes('hours') || message.includes('when')) {
      return `**Our Support Hours:**\n🌍 Monday-Friday: 24/5 (Global coverage)\n📱 Live Chat: Available now\n📧 Email: Replied within 2-4 hours\n💬 Telegram: Instant responses\n\n**Need immediate help?** I'm here 24/7!`;
    }
    
    // Default responses for unrecognized queries
    const defaultResponses = [
      `That's a great question! 🤔 Let me help you find the right information. Could you be more specific about what you'd like to know regarding our partnership programs?`,
      `I want to make sure I give you the best answer! 💡 Are you asking about PAMM accounts, capital requirements, profit sharing, or something else?`,
      `Thanks for reaching out! 😊 Our partnership models (PAMM/MAM/Pool) each have unique benefits. Which one interests you most?`,
      `I'm here to help with all your partnership questions! 🚀 Would you like to know about getting started, requirements, or how our profit sharing works?`
    ];
    
    return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
  }

  showTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
      indicator.classList.add('show');
      this.scrollToBottom();
    }
  }

  hideTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
      indicator.classList.remove('show');
    }
  }

  scrollToBottom() {
    const messagesContainer = document.getElementById('chatMessages');
    if (messagesContainer) {
      setTimeout(() => {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }, 100);
    }
  }

  showWelcomeNotification() {
    setTimeout(() => {
      const notification = document.getElementById('chatNotification');
      if (notification && !this.isOpen) {
        notification.classList.add('show');
      }
    }, 3000);
  }

  hideFloatingNotification() {
    const floatingNotification = document.getElementById('chatFloatingNotification');
    if (floatingNotification) {
      floatingNotification.style.display = 'none';
    }
  }

  getAgentImageUrl() {
    return document.querySelector('.agent-image')?.src || '/wp-content/themes/forexdrift-theme-final/assets/images/chat-agent.jpg';
  }

  saveChatHistory() {
    try {
      localStorage.setItem('forexdrift_chat_history', JSON.stringify(this.messages));
    } catch (e) {
      console.warn('Could not save chat history:', e);
    }
  }

  // Form handling methods
  bindFormEvents() {
    // Form navigation buttons
    document.querySelectorAll('.form-next-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const nextStep = parseInt(e.target.getAttribute('data-next'));
        console.log(`Next button clicked, going to step ${nextStep}`);
        if (this.validateCurrentStep()) {
          this.goToStep(nextStep);
        } else {
          console.log('Validation failed, staying on current step');
        }
      });
    });

    document.querySelectorAll('.form-back-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const prevStep = parseInt(e.target.getAttribute('data-back'));
        console.log(`Back button clicked, going to step ${prevStep}`);
        this.goToStep(prevStep);
      });
    });

    // Form submission
    const contactForm = document.getElementById('contactDetailsForm');
    if (contactForm) {
      contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.submitContactForm();
      });
    }
  }

  validateCurrentStep() {
    const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`);
    if (!currentStepElement) return false;

    const input = currentStepElement.querySelector('input, select');
    if (!input) return false;

    const isValid = input.checkValidity() && input.value.trim() !== '';
    
    // Show validation message
    const validation = currentStepElement.querySelector('.input-validation');
    if (validation) {
      if (isValid) {
        validation.textContent = '';
        validation.style.color = 'green';
      } else {
        validation.textContent = 'This field is required';
        validation.style.color = 'red';
      }
    }

    return isValid;
  }

  goToStep(stepNumber) {
    console.log(`Going to step ${stepNumber}`);
    
    // Force hide ALL steps first
    document.querySelectorAll('.form-step').forEach(step => {
      step.classList.remove('active');
      step.style.display = 'none';
      step.style.visibility = 'hidden';
      step.style.height = '0';
      step.style.opacity = '0';
    });

    // Small delay to ensure DOM update
    setTimeout(() => {
      // Show target step
      const targetStep = document.querySelector(`[data-step="${stepNumber}"]`);
      if (targetStep) {
        targetStep.classList.add('active');
        targetStep.style.display = 'flex';
        targetStep.style.visibility = 'visible';
        targetStep.style.height = 'auto';
        targetStep.style.opacity = '1';
        
        this.currentStep = stepNumber;
        console.log(`Activated step ${stepNumber}`);

        // Update progress
        this.updateProgress();

        // Focus on input
        const input = targetStep.querySelector('input, select');
        if (input) {
          setTimeout(() => input.focus(), 150);
        }
      }
    }, 50);
  }

  updateProgress() {
    const progressFill = document.querySelector('.progress-fill');
    const progressDots = document.querySelectorAll('.dot');

    if (progressFill) {
      progressFill.style.width = `${(this.currentStep / 4) * 100}%`;
    }

    progressDots.forEach((dot, index) => {
      if (index < this.currentStep) {
        dot.classList.add('active');
      } else {
        dot.classList.remove('active');
      }
    });
  }

  async submitContactForm() {
    // Validate all fields
    const formData = {
      name: document.getElementById('userName')?.value?.trim(),
      email: document.getElementById('userEmail')?.value?.trim(),
      phone: document.getElementById('userPhone')?.value?.trim(),
      country: document.getElementById('userCountry')?.value?.trim()
    };

    // Validation
    if (!formData.name || !formData.email || !formData.phone || !formData.country) {
      alert('Please fill in all required fields.');
      return;
    }

    // Submit to backend
    try {
      const response = await fetch(whispAjax.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'submit_chat_lead',
          nonce: whispAjax.leadNonce,
          ...formData
        })
      });

      const result = await response.json();
      
      if (result.success) {
        this.leadId = result.data.lead_id;
        this.formCompleted = true;
        this.activateChat();
        localStorage.setItem('whisp_lead_id', this.leadId);
        localStorage.setItem('whisp_form_completed', 'true');
      } else {
        console.error('Form submission error:', result.data);
        alert('There was an error submitting your information. Please try again.');
      }
    } catch (error) {
      console.error('Network error:', error);
      alert('Network error. Please check your connection and try again.');
    }
  }

  activateChat() {
    // Hide form
    const formContainer = document.getElementById('leadCaptureForm');
    const chatContainer = document.getElementById('chatConversation');

    if (formContainer) {
      formContainer.style.display = 'none';
    }
    
    if (chatContainer) {
      chatContainer.style.display = 'block';
      
      // Update welcome message with user name
      const userName = document.getElementById('userName')?.value?.trim();
      const userNameSpan = document.getElementById('chatUserName');
      if (userNameSpan && userName) {
        userNameSpan.textContent = userName;
      }
    }

    // Load chat history now that form is completed
    this.loadChatHistory();
  }

  initializeForm() {
    console.log('Initializing form...');
    
    // Force hide ALL steps
    document.querySelectorAll('.form-step').forEach(step => {
      step.classList.remove('active');
      step.style.display = 'none';
      step.style.visibility = 'hidden';
      step.style.height = '0';
      step.style.opacity = '0';
    });
    
    // Show only first step
    const firstStep = document.querySelector('[data-step="1"]');
    if (firstStep) {
      firstStep.classList.add('active');
      firstStep.style.display = 'flex';
      firstStep.style.visibility = 'visible';
      firstStep.style.height = 'auto';
      firstStep.style.opacity = '1';
      console.log('First step activated');
    }
    
    // Reset progress
    this.currentStep = 1;
    this.updateProgress();
  }

  checkFormCompletionStatus() {
    const savedLeadId = localStorage.getItem('whisp_lead_id');
    const formCompleted = localStorage.getItem('whisp_form_completed');
    
    if (savedLeadId && formCompleted === 'true') {
      this.leadId = savedLeadId;
      this.formCompleted = true;
      this.activateChat();
      this.loadChatHistory();
    } else {
      // Initialize form if not completed
      this.initializeForm();
    }
  }

  async sendMessageToBackend(message) {
    try {
      const response = await fetch(whispAjax.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'send_chat_message',
          nonce: whispAjax.messageNonce,
          lead_id: this.leadId,
          message: message
        })
      });

      const result = await response.json();
      
      this.hideTypingIndicator();
      
      if (result.success && result.data.response) {
        this.addMessage(result.data.response, 'bot');
      } else {
        // Fallback to local response
        const fallbackResponse = "Thank you for your message! Our partnership team will contact you shortly to discuss your inquiry in detail.";
        this.addMessage(fallbackResponse, 'bot');
      }
    } catch (error) {
      console.error('Message send error:', error);
      this.hideTypingIndicator();
      const errorResponse = "Thank you for your message! Our partnership team will contact you shortly to discuss your inquiry in detail.";
      this.addMessage(errorResponse, 'bot');
    }
  }

  getNonce() {
    // Use the localized nonce from WordPress
    return whispAjax?.nonce || 'fallback_nonce';
  }

  loadChatHistory() {
    if (!this.formCompleted) return;
    
    try {
      const savedMessages = localStorage.getItem('whisp_chat_history');
      if (savedMessages) {
        this.messages = JSON.parse(savedMessages);
        // Only load recent messages (last 10)
        this.messages = this.messages.slice(-10);
        this.renderChatHistory();
      }
    } catch (e) {
      console.warn('Could not load chat history:', e);
    }
  }

  renderChatHistory() {
    if (this.messages.length === 0) return;

    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;

    // Clear existing messages except welcome and quick actions
    const existingMessages = messagesContainer.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());

    // Render saved messages
    this.messages.forEach(messageData => {
      const messageElement = document.createElement('div');
      messageElement.className = `message ${messageData.sender}`;
      messageElement.innerHTML = `
        ${messageData.sender === 'bot' ? `
          <div class="message-avatar">
            <img src="${this.getAgentImageUrl()}" alt="Agent">
          </div>
        ` : ''}
        <div class="message-wrapper">
          <div class="message-content">${this.formatMessage(messageData.text)}</div>
          <div class="message-time">${messageData.timestamp}</div>
        </div>
        ${messageData.sender === 'user' ? `
          <div class="message-avatar">
            <div style="width: 32px; height: 32px; background: var(--brand); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #000; font-weight: bold; font-size: 14px;">
              U
            </div>
          </div>
        ` : ''}
      `;

      messagesContainer.appendChild(messageElement);
    });

    // Hide quick actions if there are user messages
    if (this.messages.some(m => m.sender === 'user')) {
      const quickActions = messagesContainer.querySelector('.quick-actions');
      if (quickActions) {
        quickActions.style.opacity = '0.5';
        quickActions.style.pointerEvents = 'none';
      }
    }

    this.scrollToBottom();
  }
}

// Initialize chat widget when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Check if chat widget exists on page
  if (document.getElementById('whisp-chat-widget')) {
    window.whispChat = new WhispChatWidget();
    console.log('Whisp Chat Widget initialized successfully');
  }
});

// Override the openLiveChat function from footer.js
window.openLiveChat = function() {
  if (window.whispChat) {
    window.whispChat.openChat();
  } else {
    console.warn('Chat widget not initialized');
  }
};

// Also check for saveChatHistory method
WhispChatWidget.prototype.saveChatHistory = function() {
  try {
    localStorage.setItem('whisp_chat_history', JSON.stringify(this.messages));
  } catch (e) {
    console.warn('Could not save chat history:', e);
  }
};
