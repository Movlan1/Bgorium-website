// Tab functionality
function initTabs() {
  const tabContainers = document.querySelectorAll('[data-tabs]');
  
  tabContainers.forEach(container => {
    const tabButtons = container.querySelectorAll('[data-tab-button]');
    // Find the parent element that contains both buttons and panels
    const parentContainer = container.parentElement;
    // Search for panels in the parent container (siblings of the tabs container)
    const tabPanels = parentContainer.querySelectorAll('[data-tab-panel]');
    
    tabButtons.forEach(button => {
      button.addEventListener('click', () => {
        const targetTab = button.getAttribute('data-tab-button');
        
        // Update button states
        tabButtons.forEach(btn => {
          if (btn.getAttribute('data-tab-button') === targetTab) {
            btn.classList.remove('text-gold-600', 'hover:text-gold-400', 'hover:bg-gold-600/10');
            btn.classList.add('bg-gold-600/20', 'text-gold-400');
          } else {
            btn.classList.remove('bg-gold-600/20', 'text-gold-400');
            btn.classList.add('text-gold-600', 'hover:text-gold-400', 'hover:bg-gold-600/10');
          }
        });
        
        // Update panel visibility
        tabPanels.forEach(panel => {
          if (panel.getAttribute('data-tab-panel') === targetTab) {
            panel.classList.remove('hidden');
          } else {
            panel.classList.add('hidden');
          }
        });
      });
    });
  });
}

// Modal functionality
function initModals() {
  // Open modal
  document.querySelectorAll('[data-modal-open]').forEach(button => {
    button.addEventListener('click', (e) => {
      const modalId = button.getAttribute('data-modal-open');
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // If button has data-card-data, populate modal with card data (for ecology page)
        const cardData = button.getAttribute('data-card-data');
        if (cardData) {
          try {
            const data = JSON.parse(cardData);
            populateModalWithCardData(modal, data);
          } catch (e) {
            console.error('Error parsing card data:', e);
          }
        }
        
        // If button has data-stake-data, populate modal with stake data (for energy page)
        const stakeData = button.getAttribute('data-stake-data');
        if (stakeData && modalId === 'stake-modal') {
          try {
            const data = JSON.parse(stakeData);
            populateStakeModal(modal, data);
          } catch (e) {
            console.error('Error parsing stake data:', e);
          }
        }
        
        // If button has data-trade-data, populate modal with trade data (for shares page)
        const tradeData = button.getAttribute('data-trade-data');
        if (tradeData && modalId === 'trade-modal') {
          try {
            const data = JSON.parse(tradeData);
            populateTradeModal(modal, data);
          } catch (e) {
            console.error('Error parsing trade data:', e);
          }
        }
        
        // If button has data-card-type, update modal title and card preview (for bank page)
        const cardType = button.getAttribute('data-card-type');
        if (cardType && modalId === 'order-card-modal') {
          const titleEl = modal.querySelector('[data-modal-title]');
          if (titleEl) {
            const cardNames = {
              'classic': 'Order Classic Card',
              'world': 'Order World Card',
              'premium': 'Order World Premium Card'
            };
            titleEl.textContent = cardNames[cardType] || 'Order Card';
          }
          
          // Update card preview box
          const cardPreview = modal.querySelector('[data-card-preview]');
          if (cardPreview) {
            const cardNameEl = cardPreview.querySelector('[data-card-name]');
            const cardIconEl = cardPreview.querySelector('[data-card-icon]');
            
            const cardData = {
              'classic': {
                name: 'Classic Card'
              },
              'world': {
                name: 'World Card'
              },
              'premium': {
                name: 'World Premium Card'
              }
            };
            
            // QrCode icon (same for all card types in preview, matching React code)
            const qrCodeIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gold-400">
              <rect width="5" height="5" x="3" y="3" rx="1"></rect>
              <rect width="5" height="5" x="16" y="3" rx="1"></rect>
              <rect width="5" height="5" x="3" y="16" rx="1"></rect>
              <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
              <path d="M21 21v.01"></path>
              <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
              <path d="M3 12h.01"></path>
              <path d="M12 3h.01"></path>
              <path d="M12 16v.01"></path>
              <path d="M16 12h1"></path>
              <path d="M21 12v.01"></path>
              <path d="M12 21v-1"></path>
            </svg>`;
            
            const card = cardData[cardType] || cardData['classic'];
            if (cardNameEl) {
              cardNameEl.textContent = card.name;
            }
            if (cardIconEl) {
              cardIconEl.innerHTML = qrCodeIcon;
            }
          }
          
          // Set up dynamic updates for cardholder name and phone
          const nameInput = modal.querySelector('#card-name-input');
          const phoneInput = modal.querySelector('#card-phone-input');
          const cardHolderNameEl = modal.querySelector('[data-card-holder-name]');
          const cardHolderPhoneEl = modal.querySelector('[data-card-holder-phone]');
          
          if (nameInput && cardHolderNameEl) {
            nameInput.addEventListener('input', (e) => {
              cardHolderNameEl.textContent = e.target.value || 'CARD HOLDER NAME';
            });
          }
          
          if (phoneInput && cardHolderPhoneEl) {
            phoneInput.addEventListener('input', (e) => {
              cardHolderPhoneEl.textContent = e.target.value || '+994 XX XXX XX XX';
            });
          }
        }
      }
    });
  });
  
  // Close modal
  document.querySelectorAll('[data-modal-close]').forEach(button => {
    button.addEventListener('click', () => {
      const modal = button.closest('[data-modal]');
      if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
      }
    });
  });
  
  // Close modal on backdrop click
  document.querySelectorAll('[data-modal]').forEach(modal => {
    // Skip stock-modal as it has its own handler
    if (modal.id === 'stock-modal') return;
    
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
      }
    });
  });
  
  // Close modal on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('[data-modal]').forEach(modal => {
        if (!modal.classList.contains('hidden')) {
          modal.classList.add('hidden');
          document.body.style.overflow = '';
        }
      });
    }
  });
}

// Populate stake modal (for energy page)
function populateStakeModal(modal, data) {
  const titleEl = modal.querySelector('[data-modal-title]');
  if (titleEl) titleEl.textContent = `Stake ${data.name || ''}`;
  
  const contentEl = modal.querySelector('[data-modal-content]');
  if (contentEl && data) {
    contentEl.innerHTML = `
      <div class="space-y-6">
        <div class="flex items-center space-x-3">
          <div class="w-16 h-16 bg-orange-600/20 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-400">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gold-400">${data.name || ''}</h3>
            <p class="text-gold-600">${data.symbol || ''}</p>
          </div>
        </div>
        <div class="bg-gold-600/10 rounded-lg p-4">
          <h4 class="text-gold-400 font-semibold mb-2">Current Price</h4>
          <p class="text-gold-400 text-2xl font-bold">${data.price || ''}</p>
          <p class="text-gold-600 text-sm mt-1">${data.change || ''}</p>
        </div>
        <div>
          <h4 class="text-gold-400 font-semibold mb-2">Staking Details</h4>
          <p class="text-gold-600 text-sm leading-relaxed">${data.description || ''}</p>
        </div>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <p class="text-gold-400 font-semibold">8.5% APY</p>
            <p class="text-gold-600 text-xs">$50-100 min stake</p>
            <p class="text-gold-600 text-xs">360 days</p>
          </div>
          <div>
            <p class="text-gold-400 font-semibold">10.5% APY</p>
            <p class="text-gold-600 text-xs">$100-200 min stake</p>
            <p class="text-gold-600 text-xs">360 days</p>
          </div>
          <div>
            <p class="text-gold-400 font-semibold">12.5% APY</p>
            <p class="text-gold-600 text-xs">$200+ min stake</p>
            <p class="text-gold-600 text-xs">360 days</p>
          </div>
        </div>
        <div class="space-y-4">
          <h4 class="text-gold-400 font-semibold">Stake Amount</h4>
          <input type="number" id="stake-amount-input" class="w-full bg-black/50 border border-gold-600/30 rounded-lg px-4 py-2 text-gold-400 focus:border-gold-500 focus:outline-none" placeholder="Enter amount in dollars" min="50" step="0.01">
          <button id="confirm-stake-btn" class="w-full bg-gradient-to-r from-gold-600 to-gold-500 hover:from-gold-500 hover:to-gold-400 text-black hover:shadow-lg hover:shadow-gold-600/30 font-medium rounded-lg px-4 py-2 text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer">
            Confirm Stake
          </button>
        </div>
      </div>
    `;
    
    // Add event listener to confirm stake button
    const confirmBtn = contentEl.querySelector('#confirm-stake-btn');
    const amountInput = contentEl.querySelector('#stake-amount-input');
    
    if (confirmBtn && amountInput) {
      confirmBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const amount = parseFloat(amountInput.value) || 0;
        
        if (amount < 50) {
          alert('Minimum stake amount is $50');
          return;
        }
        
        // Determine which APY tier based on amount
        let apy = '8.5%';
        if (amount >= 200) {
          apy = '12.5%';
        } else if (amount >= 100) {
          apy = '10.5%';
        }
        
        // Get current account balance
        const accounts = loadBankAccounts();
        const currentAccount = accounts.find(acc => acc.accountType === 'current');
        
        if (!currentAccount) {
          alert('No current account found. Please set up a current account first.');
          return;
        }
        
        if (currentAccount.balance < amount) {
          alert(`Insufficient balance. You need $${amount.toFixed(2)} but only have $${currentAccount.balance.toFixed(2)}.`);
          return;
        }
        
        // Deduct from current account
        currentAccount.balance -= amount;
        
        // Save accounts using user-specific storage
        const allAccounts = loadBankAccounts();
        const accountIndex = allAccounts.findIndex(acc => acc.id === currentAccount.id);
        if (accountIndex !== -1) {
          allAccounts[accountIndex] = currentAccount;
        } else {
          allAccounts.push(currentAccount);
        }
        
        // Save using the saveAccounts function from bank page
        const userId = getCurrentUserId();
        if (userId && typeof saveUserAccounts === 'function') {
          saveUserAccounts(userId, allAccounts);
        } else {
          // Fallback
          const userKey = getUserStorageKey('bankAccounts');
          localStorage.setItem(userKey, JSON.stringify(allAccounts));
        }
        
        // Update header balance
        if (window.updateHeaderBalance) {
          window.updateHeaderBalance();
        }
        
        alert(`Successfully staked $${amount.toFixed(2)} in ${data.name || ''} at ${apy} APY for 360 days!`);
        
        // Close modal
        modal.classList.add('hidden');
        document.body.style.overflow = '';
      });
    }
  }
}

// Populate trade modal (for shares page)
function populateTradeModal(modal, data) {
  const titleEl = modal.querySelector('[data-modal-title]');
  if (titleEl) titleEl.textContent = `Trade ${data.symbol || ''}`;
  
  const contentEl = modal.querySelector('[data-modal-content]');
  if (contentEl && data) {
    const changeColor = data.change && data.change.includes('-') ? 'text-red-400' : 'text-green-400';
    contentEl.innerHTML = `
      <div class="space-y-6">
        <div class="flex items-center space-x-3">
          <div class="w-16 h-16 bg-gold-600/20 rounded-lg flex items-center justify-center text-4xl">
            ${data.symbol === 'TSLA' ? '🚗' : data.symbol === 'AAPL' ? '🍎' : data.symbol === 'SHEL' ? '🛢️' : '📈'}
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gold-400">${data.name || ''}</h3>
            <p class="text-gold-600">${data.symbol || ''}</p>
          </div>
        </div>
        <div class="bg-gold-600/10 rounded-lg p-4">
          <h4 class="text-gold-400 font-semibold mb-2">Current Price</h4>
          <p class="text-gold-400 text-2xl font-bold">${data.price || ''}</p>
          <p class="${changeColor} text-sm mt-1 font-semibold">${data.change || ''}</p>
          <span class="inline-block mt-2 px-2 py-1 rounded text-xs font-medium ${data.sector === 'Technology' ? 'text-blue-400 bg-blue-600/20' : data.sector === 'Energy' ? 'text-orange-400 bg-orange-600/20' : 'text-green-400 bg-green-600/20'}">${data.sector || ''}</span>
        </div>
        <div>
          <h4 class="text-gold-400 font-semibold mb-2">Company Description</h4>
          <p class="text-gold-600 text-sm leading-relaxed">${data.description || ''}</p>
        </div>
        <div class="space-y-4">
          <h4 class="text-gold-400 font-semibold">Trade Options</h4>
          <div class="flex space-x-2">
            <button class="flex-1 bg-gradient-to-r from-gold-600 to-gold-500 hover:from-gold-500 hover:to-gold-400 text-black hover:shadow-lg hover:shadow-gold-600/30 font-medium rounded-lg px-4 py-2 text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer">Buy Shares</button>
            <button class="flex-1 bg-gold-600/20 hover:bg-gold-600/30 text-gold-400 border border-gold-600/30 hover:border-gold-500/50 font-medium rounded-lg px-4 py-2 text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer">Sell Shares</button>
          </div>
          <div>
            <label class="block text-gold-400 text-sm font-medium mb-2">Number of Shares</label>
            <input type="number" class="w-full bg-black/50 border border-gold-600/30 rounded-lg px-4 py-2 text-gold-400 focus:border-gold-500 focus:outline-none" placeholder="0" min="1">
          </div>
          <button class="w-full border border-gold-600 text-gold-400 hover:bg-gold-600/10 hover:border-gold-500 font-medium rounded-lg px-4 py-2 text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer">
            Add to Watchlist
          </button>
        </div>
      </div>
    `;
  }
}

// Populate modal with card data (for ecology page)
function populateModalWithCardData(modal, data) {
  // Update modal title
  const titleEl = modal.querySelector('[data-modal-title]');
  if (titleEl) titleEl.textContent = data.title || '';
  
  // Update modal content
  const contentEl = modal.querySelector('[data-modal-content]');
  if (contentEl && data) {
    contentEl.innerHTML = `
      <div class="space-y-6">
        <div class="flex items-center space-x-3">
          <div class="w-16 h-16 bg-green-600/20 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400">
              <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
              <path d="M2 17l10 5 10-5"></path>
              <path d="M2 12l10 5 10-5"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gold-400">${data.title || ''}</h3>
            <p class="text-gold-600">${data.location || ''}</p>
          </div>
        </div>
        <div class="bg-gold-600/10 rounded-lg p-4">
          <h4 class="text-gold-400 font-semibold mb-2">Environmental Impact</h4>
          <p class="text-gold-600">${data.impact || ''}</p>
        </div>
        <div>
          <h4 class="text-gold-400 font-semibold mb-2">Project Details</h4>
          <p class="text-gold-600 text-sm leading-relaxed">${data.details || ''}</p>
        </div>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div class="bg-black/30 rounded-lg p-3">
            <p class="text-gold-400 font-semibold">${data.raised || ''}</p>
            <p class="text-gold-600 text-xs">Raised</p>
          </div>
          <div class="bg-black/30 rounded-lg p-3">
            <p class="text-gold-400 font-semibold">${data.participants || ''}</p>
            <p class="text-gold-600 text-xs">Investors</p>
          </div>
          <div class="bg-black/30 rounded-lg p-3">
            <p class="text-gold-400 font-semibold">${data.progress || ''}%</p>
            <p class="text-gold-600 text-xs">Complete</p>
          </div>
        </div>
        <div class="space-y-4">
          <h4 class="text-gold-400 font-semibold">Investment Options</h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button data-invest-amount="100" class="px-3 py-1.5 text-sm border border-gold-600 text-gold-400 hover:bg-gold-600/10 hover:border-gold-500 font-medium rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer invest-option-btn">$100</button>
            <button data-invest-amount="500" class="px-3 py-1.5 text-sm border border-gold-600 text-gold-400 hover:bg-gold-600/10 hover:border-gold-500 font-medium rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer invest-option-btn">$500</button>
            <button data-invest-amount="1000" class="px-3 py-1.5 text-sm border border-gold-600 text-gold-400 hover:bg-gold-600/10 hover:border-gold-500 font-medium rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer invest-option-btn">$1,000</button>
          </div>
          <div>
            <label class="block text-gold-400 text-sm font-medium mb-2">Or Enter Custom Amount</label>
            <input type="number" id="invest-custom-amount" step="0.01" min="0" class="w-full bg-black/50 border border-gold-600/30 rounded-lg px-4 py-2 text-gold-400 focus:border-gold-500 focus:outline-none" placeholder="0.00">
          </div>
          <button id="invest-now-btn" class="w-full bg-gradient-to-r from-gold-600 to-gold-500 hover:from-gold-500 hover:to-gold-400 text-black hover:shadow-lg hover:shadow-gold-600/30 font-medium rounded-lg px-4 py-2 text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gold-400/50 cursor-pointer">
            Invest Now
          </button>
        </div>
      </div>
    `;
    
    // Initialize investment functionality after modal content is populated
    setTimeout(() => {
      initInvestModal();
    }, 100);
  }
}

// Initialize investment modal functionality
function initInvestModal() {
  const investModal = document.getElementById('invest-modal');
  if (!investModal) return;
  
  let selectedAmount = 0;
  
  // Handle investment option button clicks
  const optionButtons = investModal.querySelectorAll('.invest-option-btn');
  optionButtons.forEach(btn => {
    // Remove existing listeners to avoid duplicates
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    
    newBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      // Remove active state from all buttons
      investModal.querySelectorAll('.invest-option-btn').forEach(b => {
        b.classList.remove('bg-gold-600/20', 'border-gold-500');
        b.classList.add('border-gold-600');
      });
      // Add active state to clicked button
      newBtn.classList.add('bg-gold-600/20', 'border-gold-500');
      newBtn.classList.remove('border-gold-600');
      
      // Set selected amount
      selectedAmount = parseFloat(newBtn.getAttribute('data-invest-amount'));
      
      // Clear custom amount input
      const customInput = investModal.querySelector('#invest-custom-amount');
      if (customInput) customInput.value = '';
    });
  });
  
  // Handle custom amount input
  const customInput = investModal.querySelector('#invest-custom-amount');
  if (customInput) {
    customInput.addEventListener('input', (e) => {
      e.stopPropagation();
      const value = parseFloat(e.target.value);
      if (!isNaN(value) && value > 0) {
        selectedAmount = value;
        // Remove active state from option buttons
        investModal.querySelectorAll('.invest-option-btn').forEach(b => {
          b.classList.remove('bg-gold-600/20', 'border-gold-500');
          b.classList.add('border-gold-600');
        });
      }
    });
  }
  
  // Handle "Invest Now" button
  const investNowBtn = investModal.querySelector('#invest-now-btn');
  if (investNowBtn) {
    // Remove existing listener to avoid duplicates
    const newInvestBtn = investNowBtn.cloneNode(true);
    investNowBtn.parentNode.replaceChild(newInvestBtn, investNowBtn);
    
    newInvestBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      
      // Get final amount (from custom input if filled, otherwise from selected button)
      let finalAmount = selectedAmount;
      if (customInput && customInput.value) {
        const customValue = parseFloat(customInput.value);
        if (!isNaN(customValue) && customValue > 0) {
          finalAmount = customValue;
        }
      }
      
      if (!finalAmount || finalAmount <= 0) {
        alert('Please select an investment amount or enter a custom amount');
        return;
      }
      
      // Get current account balance
      const accounts = loadBankAccounts();
      const currentAccount = accounts.find(acc => acc.accountType === 'current');
      
      if (!currentAccount) {
        alert('No current account found. Please set up a current account first.');
        return;
      }
      
      if (currentAccount.balance < finalAmount) {
        alert('Insufficient balance. Please check your account balance.');
        return;
      }
      
      // Deduct investment amount from current account
      currentAccount.balance -= finalAmount;
      
      // Save accounts using user-specific storage
      const allAccounts = loadBankAccounts();
      const accountIndex = allAccounts.findIndex(acc => acc.id === currentAccount.id);
      if (accountIndex !== -1) {
        allAccounts[accountIndex] = currentAccount;
      } else {
        allAccounts.push(currentAccount);
      }
      
      // Save using the saveAccounts function from bank page
      const userId = getCurrentUserId();
      if (userId && typeof saveUserAccounts === 'function') {
        saveUserAccounts(userId, allAccounts);
      } else {
        // Fallback
        const userKey = getUserStorageKey('bankAccounts');
        localStorage.setItem(userKey, JSON.stringify(allAccounts));
      }
      
      // Update header balance
      if (window.updateHeaderBalance) {
        window.updateHeaderBalance();
      }
      
      // Show success message
      alert(`Successfully invested $${finalAmount.toFixed(2)} in the project!`);
      
      // Close modal
      investModal.classList.add('hidden');
      document.body.style.overflow = '';
      
      // Reset form
      selectedAmount = 0;
      investModal.querySelectorAll('.invest-option-btn').forEach(b => {
        b.classList.remove('bg-gold-600/20', 'border-gold-500');
        b.classList.add('border-gold-600');
      });
      if (customInput) customInput.value = '';
    });
  }
  
  // Stop propagation on modal content to prevent closing when clicking inside
  const modalContent = investModal.querySelector('div.rounded-xl');
  if (modalContent) {
    modalContent.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }
  
  // Stop propagation on form inputs
  const formInputs = investModal.querySelectorAll('input, button');
  formInputs.forEach(input => {
    input.addEventListener('click', (e) => {
      e.stopPropagation();
    });
    input.addEventListener('focus', (e) => {
      e.stopPropagation();
    });
  });
}

// Language dropdown functionality
function initLanguageDropdown() {
  const languageData = {
    en: { flag: '🇺🇸', name: 'English', code: 'EN' },
    az: { flag: '🇦🇿', name: 'Azərbaycan', code: 'AZ' },
    tr: { flag: '🇹🇷', name: 'Türkçe', code: 'TR' },
    ru: { flag: '🇷🇺', name: 'Русский', code: 'RU' },
    ar: { flag: '🇸🇦', name: 'العربية', code: 'AR' },
    fr: { flag: '🇫🇷', name: 'Français', code: 'FR' },
    de: { flag: '🇩🇪', name: 'Deutsch', code: 'DE' },
    es: { flag: '🇪🇸', name: 'Español', code: 'ES' }
  };
  
  // Get saved language or default to 'en'
  let currentLanguage = getUserItem('selectedLanguage') || 'en';
  
  const dropdowns = document.querySelectorAll('[data-language-dropdown]');
  
  dropdowns.forEach(dropdown => {
    const button = dropdown.querySelector('[data-language-button]');
    const menu = dropdown.querySelector('[data-language-menu]');
    const backdrop = dropdown.querySelector('[data-language-backdrop]');
    const display = dropdown.querySelector('[data-language-display]');
    const chevron = dropdown.querySelector('[data-language-chevron]');
    const options = dropdown.querySelectorAll('[data-language-option]');
    
    // Update display with current language
    const updateDisplay = (lang) => {
      const langData = languageData[lang];
      if (display && langData) {
        display.textContent = `${langData.flag} ${langData.code}`;
      }
      
      // Update active state in menu
      options.forEach(opt => {
        const optLang = opt.getAttribute('data-language-option');
        if (optLang === lang) {
          opt.classList.remove('text-gold-600');
          opt.classList.add('bg-gold-600/20', 'text-gold-400');
        } else {
          opt.classList.remove('bg-gold-600/20', 'text-gold-400');
          opt.classList.add('text-gold-600');
        }
      });
    };
    
    // Initialize display
    updateDisplay(currentLanguage);
    
    // Toggle dropdown
    button.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = !menu.classList.contains('hidden');
      
      if (isOpen) {
        menu.classList.add('hidden');
        backdrop.classList.add('hidden');
        chevron.classList.remove('rotate-180');
      } else {
        menu.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        chevron.classList.add('rotate-180');
      }
    });
    
    // Handle language selection
    options.forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        const selectedLang = option.getAttribute('data-language-option');
        currentLanguage = selectedLang;
        setUserItem('selectedLanguage', selectedLang);
        updateDisplay(selectedLang);
        menu.classList.add('hidden');
        backdrop.classList.add('hidden');
        chevron.classList.remove('rotate-180');
      });
    });
    
    // Close on backdrop click
    backdrop.addEventListener('click', () => {
      menu.classList.add('hidden');
      backdrop.classList.add('hidden');
      chevron.classList.remove('rotate-180');
    });
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        backdrop.classList.add('hidden');
        chevron.classList.remove('rotate-180');
      }
    });
  });
}

// Forecast page functionality
function initForecastPage() {
  // Only run on forecast page
  if (!document.querySelector('[data-forecast-timeframes]')) return;
  
  const currentPrice = 82.45;
  let selectedDays = 7;
  let isLoading = false;
  
  const forecastData = {
    7: { accuracy: 85, predictedPrice: 85.20, confidence: 78, trend: 'bullish' },
    14: { accuracy: 78, predictedPrice: 84.50, confidence: 75, trend: 'bullish' },
    30: { accuracy: 72, predictedPrice: 83.80, confidence: 70, trend: 'bullish' },
    90: { accuracy: 65, predictedPrice: 82.90, confidence: 65, trend: 'stable' }
  };
  
  const factors = [
    ['OPEC+ production cuts', 'Increased global demand', 'Geopolitical tensions', 'Seasonal consumption patterns'],
    ['OPEC+ production decisions', 'Global economic indicators', 'Seasonal demand patterns', 'Inventory level changes'],
    ['Currency fluctuations', 'Renewable energy adoption', 'Market sentiment', 'Supply chain disruptions'],
    ['Long-term economic trends', 'Energy transition policies', 'Global trade patterns', 'Climate regulations']
  ];
  
  function updateForecastData(days) {
    selectedDays = days;
    const data = forecastData[days];
    if (!data) return;
    
    // Update confidence
    const confidenceEl = document.querySelector('[data-forecast-confidence]');
    if (confidenceEl) {
      confidenceEl.textContent = `${data.confidence}%`;
    }
    
    // Update days text
    const daysTextEl = document.querySelector('[data-forecast-days-text]');
    if (daysTextEl) {
      daysTextEl.textContent = `${days} day forecast`;
    }
    
    // Update predicted price
    const priceEl = document.querySelector('[data-forecast-price]');
    if (priceEl) {
      priceEl.textContent = `$${data.predictedPrice.toFixed(2)}`;
    }
    
    // Update trend
    const trendEl = document.querySelector('[data-forecast-trend]');
    if (trendEl) {
      trendEl.textContent = `${data.trend} trend`;
      trendEl.className = `text-sm mt-1 ${data.trend === 'bullish' ? 'text-green-400' : data.trend === 'bearish' ? 'text-red-400' : 'text-gold-400'}`;
    }
    
    // Update price change
    const change = ((data.predictedPrice - currentPrice) / currentPrice) * 100;
    const changeEl = document.querySelector('[data-forecast-change]');
    if (changeEl) {
      changeEl.textContent = `${change >= 0 ? '+' : ''}${change.toFixed(2)}%`;
      changeEl.className = `text-2xl font-bold ${change >= 0 ? 'text-green-400' : 'text-red-400'}`;
    }
    
    // Update factors based on days
    const factorsEl = document.querySelector('[data-forecast-factors]');
    if (factorsEl) {
      const factorIndex = days === 7 ? 0 : days === 14 ? 1 : days === 30 ? 2 : 3;
      factorsEl.innerHTML = factors[factorIndex].map(factor => `
        <li class="text-gold-600 text-sm flex items-center space-x-2">
          <span class="text-gold-400">•</span>
          <span>${factor}</span>
        </li>
      `).join('');
    }
    
    // Update chart
    updateChart(days);
  }
  
  function updateChart(days) {
    const chartBars = document.querySelector('[data-chart-bars]');
    if (!chartBars) return;
    
    // Clear existing bars
    chartBars.innerHTML = '';
    
    // Generate bars for the selected timeframe
    // For 7 days: show 7 bars (Now + 6 days)
    // For longer timeframes: show representative bars
    let numBars = 7;
    if (days === 14) numBars = 7; // Show weekly points
    else if (days === 30) numBars = 7; // Show weekly points
    else if (days === 90) numBars = 7; // Show monthly points
    
    const baseHeight = 30;
    const historicalBars = Math.min(4, Math.floor(numBars * 0.6)); // First 60% are historical
    
    for (let i = 0; i < numBars; i++) {
      const isFirst = i === 0;
      const isPredicted = i >= historicalBars;
      // Generate heights that trend upward for predicted values
      const progress = i / (numBars - 1);
      const height = baseHeight + (progress * 55) + (Math.random() * 10 - 5);
      const clampedHeight = Math.max(25, Math.min(95, height));
      
      let label = '';
      if (isFirst) {
        label = 'Now';
      } else if (days === 7) {
        label = `+${i}d`;
      } else if (days === 14) {
        label = `+${i * 2}d`;
      } else if (days === 30) {
        label = `+${Math.round(i * 4.3)}d`;
      } else {
        label = `+${Math.round(i * 13)}d`;
      }
      
      const barDiv = document.createElement('div');
      barDiv.className = 'flex flex-col items-center space-y-2';
      barDiv.innerHTML = `
        <div class="w-6 rounded-t ${isFirst ? 'bg-gold-400' : isPredicted ? 'bg-blue-400/70' : 'bg-gold-600/70'}" style="height: ${clampedHeight}%"></div>
        <span class="text-xs text-gold-600">${label}</span>
      `;
      chartBars.appendChild(barDiv);
    }
  }
  
  function refreshForecast() {
    if (isLoading) return;
    
    isLoading = true;
    const chartBars = document.querySelector('[data-chart-bars]');
    const chartLoading = document.querySelector('[data-chart-loading]');
    const refreshButton = document.querySelector('[data-refresh-forecast]');
    
    // Show loading state
    if (chartBars) chartBars.style.opacity = '0.3';
    if (chartLoading) {
      chartLoading.classList.remove('hidden');
      chartLoading.style.display = 'flex';
    }
    if (refreshButton) {
      refreshButton.disabled = true;
      refreshButton.textContent = 'Analyzing...';
      refreshButton.style.opacity = '0.6';
      refreshButton.style.cursor = 'not-allowed';
    }
    
    // Simulate AI analysis (2 seconds)
    setTimeout(() => {
      // Generate new random forecast data
      const variation = 0.05;
      const direction = Math.random() > 0.5 ? 1 : -1;
      const multiplier = selectedDays / 7;
      const newPrice = currentPrice + (currentPrice * variation * direction * multiplier);
      const newConfidence = Math.max(50, 90 - (selectedDays / 7) * 5);
      const newTrend = newPrice > currentPrice ? 'bullish' : 'bearish';
      
      // Update forecast data
      forecastData[selectedDays] = {
        accuracy: forecastData[selectedDays].accuracy,
        predictedPrice: parseFloat(newPrice.toFixed(2)),
        confidence: Math.round(newConfidence),
        trend: newTrend
      };
      
      // Update UI
      updateForecastData(selectedDays);
      
      // Hide loading state
      if (chartBars) chartBars.style.opacity = '1';
      if (chartLoading) {
        chartLoading.classList.add('hidden');
        chartLoading.style.display = 'none';
      }
      if (refreshButton) {
        refreshButton.disabled = false;
        refreshButton.textContent = 'Refresh Forecast';
        refreshButton.style.opacity = '1';
        refreshButton.style.cursor = 'pointer';
      }
      
      isLoading = false;
    }, 2000);
  }
  
  // Initialize timeframe buttons
  const timeframeButtons = document.querySelectorAll('[data-forecast-days]');
  timeframeButtons.forEach(button => {
    button.addEventListener('click', () => {
      const days = parseInt(button.getAttribute('data-forecast-days'));
      
      // Update button styles
      timeframeButtons.forEach(btn => {
        const btnDays = parseInt(btn.getAttribute('data-forecast-days'));
        if (btnDays === days) {
          // Active button - primary style
          btn.classList.remove('border', 'border-gold-600', 'text-gold-400', 'hover:bg-gold-600/10', 'hover:border-gold-500');
          btn.classList.add('bg-gradient-to-r', 'from-gold-600', 'to-gold-500', 'hover:from-gold-500', 'hover:to-gold-400', 'text-black', 'hover:shadow-lg', 'hover:shadow-gold-600/30');
        } else {
          // Inactive button - outline style
          btn.classList.remove('bg-gradient-to-r', 'from-gold-600', 'to-gold-500', 'hover:from-gold-500', 'hover:to-gold-400', 'text-black', 'hover:shadow-lg', 'hover:shadow-gold-600/30');
          btn.classList.add('border', 'border-gold-600', 'text-gold-400', 'hover:bg-gold-600/10', 'hover:border-gold-500');
        }
      });
      
      // Update forecast data
      updateForecastData(days);
    });
  });
  
  // Initialize refresh button
  const refreshButton = document.querySelector('[data-refresh-forecast]');
  if (refreshButton) {
    refreshButton.addEventListener('click', refreshForecast);
  }
  
  // Initialize chart
  updateChart(selectedDays);
}

// Profile page functionality
function initProfilePage() {
  // Load saved name from user storage and update profile fields
  const savedName = getUserItem('userName');
  if (savedName) {
    // Update profile display if on profile page
    const profileNameDisplay = document.querySelector('[data-profile-field="name"][data-profile-display]');
    if (profileNameDisplay) {
      profileNameDisplay.textContent = savedName;
    }
    const profileNameInput = document.querySelector('[data-profile-field="name"][data-profile-input]');
    if (profileNameInput) {
      profileNameInput.value = savedName;
    }
  }
  
  // Profile edit mode toggle
  const editToggle = document.querySelector('[data-profile-edit-toggle]');
  const profileForm = document.querySelector('[data-profile-form]');
  let isEditMode = false;
  const originalValues = {};
  
  if (editToggle && profileForm) {
    // Store original values
    document.querySelectorAll('[data-profile-display]').forEach(display => {
      const field = display.getAttribute('data-profile-field');
      originalValues[field] = display.textContent.trim();
    });
    
    editToggle.addEventListener('click', () => {
      isEditMode = !isEditMode;
      
      if (isEditMode) {
        // Enter edit mode
        editToggle.textContent = 'Cancel';
        editToggle.classList.remove('bg-gold-600/20');
        editToggle.classList.add('border', 'border-gold-600/30', 'hover:bg-gold-600/30');
        
        // Show inputs, hide displays
        document.querySelectorAll('[data-profile-display]').forEach(display => {
          display.classList.add('hidden');
        });
        document.querySelectorAll('[data-profile-input]').forEach(input => {
          input.classList.remove('hidden');
        });
        
        // Show save button
        const saveButton = document.querySelector('[data-profile-save]');
        if (saveButton) {
          saveButton.classList.remove('hidden');
        }
      } else {
        // Exit edit mode
        editToggle.textContent = 'Edit';
        editToggle.classList.add('bg-gold-600/20');
        editToggle.classList.remove('border', 'border-gold-600/30', 'hover:bg-gold-600/30');
        
        // Restore original values
        document.querySelectorAll('[data-profile-input]').forEach(input => {
          const field = input.getAttribute('data-profile-field');
          input.value = originalValues[field];
        });
        
        // Show displays, hide inputs
        document.querySelectorAll('[data-profile-display]').forEach(display => {
          display.classList.remove('hidden');
        });
        document.querySelectorAll('[data-profile-input]').forEach(input => {
          input.classList.add('hidden');
        });
        
        // Hide save button
        const saveButton = document.querySelector('[data-profile-save]');
        if (saveButton) {
          saveButton.classList.add('hidden');
        }
      }
    });
    
    // Save changes
    const saveButton = document.querySelector('[data-profile-save]');
    if (saveButton) {
      saveButton.addEventListener('click', () => {
        // Update display values from inputs
        document.querySelectorAll('[data-profile-input]').forEach(input => {
          const field = input.getAttribute('data-profile-field');
          const display = document.querySelector(`[data-profile-field="${field}"][data-profile-display]`);
          if (display) {
            const newValue = input.value;
            display.textContent = newValue;
            originalValues[field] = newValue;
            
            // If this is the name field, also update the header and save to localStorage
            if (field === 'name') {
              // Save to user storage for persistence across pages
              setUserItem('userName', newValue);
              
              // Also update in auth system
              const userId = getCurrentUserId();
              if (userId && typeof updateUserProperty === 'function') {
                updateUserProperty(userId, 'name', newValue);
              }
              
              // Update header on current page
              const headerNameElements = document.querySelectorAll('[data-header-name]');
              headerNameElements.forEach(el => {
                el.textContent = `Welcome, ${newValue}`;
              });
            }
          }
        });
        
        // Exit edit mode
        isEditMode = false;
        editToggle.textContent = 'Edit';
        editToggle.classList.add('bg-gold-600/20');
        editToggle.classList.remove('border', 'border-gold-600/30', 'hover:bg-gold-600/30');
        
        // Show displays, hide inputs
        document.querySelectorAll('[data-profile-display]').forEach(display => {
          display.classList.remove('hidden');
        });
        document.querySelectorAll('[data-profile-input]').forEach(input => {
          input.classList.add('hidden');
        });
        
        // Hide save button
        saveButton.classList.add('hidden');
        
        // Show success message (optional)
        // You could add a toast notification here
      });
    }
  }
  
  // Settings preferences
  // Language selection
  const languageSelect = document.querySelector('[data-settings-language]');
  if (languageSelect) {
    // Load saved language from user storage
    const savedLanguage = getUserItem('selectedLanguage') || 'en';
    languageSelect.value = savedLanguage;
    
    languageSelect.addEventListener('change', (e) => {
      const selectedLang = e.target.value;
      setUserItem('selectedLanguage', selectedLang);
      // Update header language dropdown if it exists
      const headerLanguageDisplay = document.querySelector('[data-language-display]');
      if (headerLanguageDisplay) {
        const languages = {
          'en': '🇺🇸 EN',
          'az': '🇦🇿 AZ',
          'tr': '🇹🇷 TR',
          'ru': '🇷🇺 RU'
        };
        headerLanguageDisplay.textContent = languages[selectedLang] || languages['en'];
      }
    });
  }
  
  // Theme toggle
  const themeButton = document.querySelector('[data-settings-theme]');
  const themeIcon = document.querySelector('[data-theme-icon]');
  if (themeButton) {
    // Load saved theme from user storage
    let currentTheme = getUserItem('theme') || 'dark';
    updateThemeButton(currentTheme, themeButton, themeIcon);
    
    themeButton.addEventListener('click', () => {
      currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
      setUserItem('theme', currentTheme);
      applyTheme(currentTheme);
      
      // Also update in auth system
      const userId = getCurrentUserId();
      if (userId && typeof updateUserProperty === 'function') {
        updateUserProperty(userId, 'theme', currentTheme);
      }
    });
  }
  
  // Notifications toggle
  const notificationsButton = document.querySelector('[data-settings-notifications]');
  if (notificationsButton) {
    // Load saved notifications state from user storage
    let notificationsEnabled = getUserItem('notifications') !== 'false'; // default true
    updateNotificationsButton(notificationsEnabled, notificationsButton);
    
    notificationsButton.addEventListener('click', () => {
      notificationsEnabled = !notificationsEnabled;
      setUserItem('notifications', notificationsEnabled.toString());
      updateNotificationsButton(notificationsEnabled, notificationsButton);
      
      // Also update in auth system
      const userId = getCurrentUserId();
      if (userId && typeof updateUserProperty === 'function') {
        updateUserProperty(userId, 'notifications', notificationsEnabled);
      }
    });
  }
  
  // Logout button
  const logoutButton = document.getElementById('logout-button');
  if (logoutButton) {
    logoutButton.addEventListener('click', () => {
      // Check if logout function exists (from auth.js)
      if (typeof logout === 'function') {
        logout();
      } else {
        // Fallback: manually clear session and redirect
        localStorage.removeItem('currentUserId');
        window.location.href = 'login.html';
      }
    });
  }
}

// Global theme management
function applyTheme(theme) {
  const html = document.documentElement;
  const body = document.body;
  
  if (theme === 'light') {
    html.classList.add('light-mode');
    html.classList.remove('dark-mode');
    body.classList.add('light-mode');
    body.classList.remove('dark-mode');
    
    // Apply light mode styles via CSS variables
    html.style.setProperty('--bg-primary', '#f8f9fa');
    html.style.setProperty('--bg-secondary', '#ffffff');
    html.style.setProperty('--text-primary', '#1a1a1a');
    html.style.setProperty('--text-secondary', '#4a5568');
    html.style.setProperty('--border-color', '#e2e8f0');
  } else {
    html.classList.add('dark-mode');
    html.classList.remove('light-mode');
    body.classList.add('dark-mode');
    body.classList.remove('light-mode');
    
    // Apply dark mode styles (default)
    html.style.setProperty('--bg-primary', '#000000');
    html.style.setProperty('--bg-secondary', '#111827');
    html.style.setProperty('--text-primary', '#fbbf24');
    html.style.setProperty('--text-secondary', '#d97706');
    html.style.setProperty('--border-color', 'rgba(217, 119, 6, 0.3)');
  }
  
  // Update theme button if it exists
  const themeButton = document.querySelector('[data-settings-theme]');
  const themeIcon = document.querySelector('[data-theme-icon]');
  if (themeButton) {
    updateThemeButton(theme, themeButton, themeIcon);
  }
}

function updateThemeButton(theme, button, icon) {
  if (theme === 'dark') {
    button.textContent = 'Dark Mode';
    button.className = 'px-3 py-1.5 border border-gold-600/30 text-gold-400 hover:bg-gold-600/10 rounded-lg text-sm transition-colors';
    if (icon) {
      // Moon icon (dark mode)
      icon.innerHTML = '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>';
    }
  } else {
    button.textContent = 'Light Mode';
    button.className = 'px-3 py-1.5 border border-gold-600/30 text-gold-400 hover:bg-gold-600/10 rounded-lg text-sm transition-colors';
    if (icon) {
      // Sun icon (light mode) - full SVG structure
      icon.innerHTML = '<circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path>';
    }
  }
}

// Initialize theme on page load
function initTheme() {
  const savedTheme = getUserItem('theme') || 'dark';
  applyTheme(savedTheme);
}

function updateNotificationsButton(enabled, button) {
  if (enabled) {
    button.textContent = 'Enabled';
    button.className = 'px-3 py-1.5 bg-gradient-to-r from-gold-600 to-gold-500 hover:from-gold-500 hover:to-gold-400 text-black font-medium rounded-lg text-sm transition-all duration-300';
  } else {
    button.textContent = 'Disabled';
    button.className = 'px-3 py-1.5 border border-gold-600/30 text-gold-400 hover:bg-gold-600/10 rounded-lg text-sm transition-colors';
  }
}

// Load saved user name on all pages
function loadUserName() {
  const savedName = getUserItem('userName');
  
  // If no saved name, try to get from current user
  if (!savedName) {
    const userId = getCurrentUserId();
    if (userId && typeof getCurrentUser === 'function') {
      const user = getCurrentUser();
      if (user && user.name) {
        setUserItem('userName', user.name);
        const headerNameElements = document.querySelectorAll('[data-header-name]');
        headerNameElements.forEach(el => {
          el.textContent = `Welcome, ${user.name}`;
        });
        return;
      }
    }
  }
  if (savedName) {
    const headerNameElements = document.querySelectorAll('[data-header-name]');
    headerNameElements.forEach(el => {
      el.textContent = `Welcome, ${savedName}`;
    });
  }
}

// Global variables for market ticker
let marketTickerInterval = null;
let marketTickerCurrentPrices = {};

// Initialize market ticker with real data
function initMarketTicker() {
  // Add data attributes to price elements if not already present
  const tickerContent = document.querySelector('[data-ticker-content]');
  if (tickerContent) {
    const items = tickerContent.querySelectorAll('.inline-flex.items-center');
    items.forEach((item, index) => {
      const symbolSpan = item.querySelector('.text-gold-400.font-semibold');
      const priceSpan = item.querySelector('.text-gold-600');
      const percentSpan = item.querySelector('.flex.items-center.space-x-1 span');
      
      if (symbolSpan && priceSpan && percentSpan) {
        const symbol = symbolSpan.textContent.trim().toLowerCase();
        if (!priceSpan.hasAttribute('data-ticker-price')) {
          priceSpan.setAttribute('data-ticker-price', '');
          priceSpan.setAttribute('data-ticker-symbol', symbol);
        }
        if (!percentSpan.hasAttribute('data-ticker-percent')) {
          percentSpan.setAttribute('data-ticker-percent', '');
          percentSpan.setAttribute('data-ticker-symbol', symbol);
        }
      }
    });
  }
  
  // Only set up interval if it doesn't exist
  if (marketTickerInterval === null) {
    // Initial fetch
    fetchAllTickerPrices();
    
    // Update every 30 seconds to avoid rate limiting (was 5 seconds)
    marketTickerInterval = setInterval(fetchAllTickerPrices, 30000);
  }
}

// Update ticker item (global function)
function updateTickerItem(symbol, price, changePercent) {
  const priceElements = document.querySelectorAll(`[data-ticker-price][data-ticker-symbol="${symbol}"]`);
  const percentElements = document.querySelectorAll(`[data-ticker-percent][data-ticker-symbol="${symbol}"]`);
  
  // Format price based on symbol
  let formattedPrice = price;
  if (symbol === 'btc' || symbol === 'eth') {
    formattedPrice = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  } else if (symbol === 'gold') {
    formattedPrice = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  } else if (symbol === 'oil') {
    formattedPrice = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  } else {
    formattedPrice = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }
  
  // Update all price elements (duplicated for scroll effect)
  priceElements.forEach(el => {
    el.textContent = formattedPrice;
  });
  
  // Format and update percent change
  const isPositive = changePercent >= 0;
  const formattedPercent = `${isPositive ? '+' : ''}${changePercent.toFixed(2)}%`;
  
  percentElements.forEach(el => {
    el.textContent = formattedPercent;
  });
  
  // Update icon and color based on change
  const changeContainers = document.querySelectorAll(`[data-ticker-percent][data-ticker-symbol="${symbol}"]`);
  changeContainers.forEach(container => {
    const changeDiv = container.closest('.flex.items-center.space-x-1');
    if (changeDiv) {
      const icon = changeDiv.querySelector('svg');
      
      if (isPositive) {
        changeDiv.className = 'flex items-center space-x-1 text-green-400';
        // Trending up icon - preserve SVG structure
        if (icon) {
          icon.innerHTML = '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline>';
        }
      } else {
        changeDiv.className = 'flex items-center space-x-1 text-red-400';
        // Trending down icon - preserve SVG structure
        if (icon) {
          icon.innerHTML = '<polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline><polyline points="16 17 22 17 22 11"></polyline>';
        }
      }
    }
  });
  
  marketTickerCurrentPrices[symbol] = price;
}

// Fetch Bitcoin and Ethereum - using multiple real APIs with fallbacks
async function fetchCryptoPrices() {
  try {
    const previousPrices = JSON.parse(getUserItem('tickerPrices') || '{}');
    
    // Try Binance public API first (no rate limits, very reliable)
    try {
      const btcResponse = await fetch('https://api.binance.com/api/v3/ticker/24hr?symbol=BTCUSDT');
      const ethResponse = await fetch('https://api.binance.com/api/v3/ticker/24hr?symbol=ETHUSDT');
      
      if (btcResponse.ok && ethResponse.ok) {
        const btcData = await btcResponse.json();
        const ethData = await ethResponse.json();
        
        // Binance returns price as string and priceChangePercent
        const btcPrice = parseFloat(btcData.lastPrice);
        const btcChange = parseFloat(btcData.priceChangePercent);
        const ethPrice = parseFloat(ethData.lastPrice);
        const ethChange = parseFloat(ethData.priceChangePercent);
        
        updateTickerItem('btc', btcPrice, btcChange);
        updateTickerItem('eth', ethPrice, ethChange);
        return; // Success, exit early
      }
    } catch (binanceError) {
      console.warn('Binance API failed, trying alternative:', binanceError);
    }
    
    // Fallback to CoinGecko via proxy
    try {
      const proxyUrl = 'https://api.allorigins.win/raw?url=';
      const apiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum&vs_currencies=usd&include_24hr_change=true';
      
      const response = await fetch(proxyUrl + encodeURIComponent(apiUrl));
      if (response.ok) {
        const data = await response.json();
        
        // Check for rate limit error
        if (data.status && data.status.error_code) {
          throw new Error('Rate limit exceeded');
        }
        
        // Update BTC
        if (data.bitcoin) {
          const btcPrice = data.bitcoin.usd;
          const btcChange = data.bitcoin.usd_24h_change || 0;
          updateTickerItem('btc', btcPrice, btcChange);
        }
        
        // Update ETH
        if (data.ethereum) {
          const ethPrice = data.ethereum.usd;
          const ethChange = data.ethereum.usd_24h_change || 0;
          updateTickerItem('eth', ethPrice, ethChange);
        }
        return; // Success
      }
    } catch (coingeckoError) {
      console.warn('CoinGecko API failed:', coingeckoError);
    }
    
    // If all APIs fail, use fallback
    throw new Error('All crypto APIs failed');
  } catch (error) {
    console.error('Error fetching crypto prices, using fallback:', error);
    useFallbackCryptoPrices();
  }
}

// Fallback crypto prices when API fails
function useFallbackCryptoPrices() {
  const previousPrices = JSON.parse(getUserItem('tickerPrices') || '{}');
  
  // BTC - simulate around $43,000-44,000 with small changes
  const prevBtc = previousPrices.btc || 43500;
  const btcChangePercent = (Math.random() * 0.5 - 0.25); // -0.25% to +0.25%
  const btcPrice = prevBtc * (1 + btcChangePercent / 100);
  const btcChange = prevBtc ? ((btcPrice - prevBtc) / prevBtc) * 100 : btcChangePercent;
  updateTickerItem('btc', btcPrice, btcChange);
  
  // ETH - simulate around $2,500-2,600 with small changes
  const prevEth = previousPrices.eth || 2550;
  const ethChangePercent = (Math.random() * 0.5 - 0.25); // -0.25% to +0.25%
  const ethPrice = prevEth * (1 + ethChangePercent / 100);
  const ethChange = prevEth ? ((ethPrice - prevEth) / prevEth) * 100 : ethChangePercent;
  updateTickerItem('eth', ethPrice, ethChange);
}

// Fetch Gold and Oil prices
async function fetchCommodityPrices() {
  try {
    const previousPrices = JSON.parse(getUserItem('tickerPrices') || '{}');
    
    // Fetch Gold price - try multiple sources
    try {
      // Try Yahoo Finance for Gold futures (GC=F)
      const proxyUrl = 'https://api.allorigins.win/raw?url=';
      const goldUrl = 'https://query1.finance.yahoo.com/v8/finance/chart/GC=F?interval=1d&range=1d';
      const goldResponse = await fetch(proxyUrl + encodeURIComponent(goldUrl));
      
      if (goldResponse.ok) {
        const goldData = await goldResponse.json();
        if (goldData.chart && goldData.chart.result && goldData.chart.result[0]) {
          const result = goldData.chart.result[0];
          const goldPrice = result.meta.regularMarketPrice;
          const previousClose = result.meta.previousClose || goldPrice;
          const goldChange = goldPrice - previousClose;
          const gold24hChange = previousClose ? ((goldChange / previousClose) * 100) : 0;
          updateTickerItem('gold', goldPrice, gold24hChange);
        } else {
          throw new Error('Invalid gold data from Yahoo');
        }
      } else {
        throw new Error('Yahoo Finance gold API failed');
      }
    } catch (goldError) {
      console.warn('Yahoo Finance gold failed, trying CoinGecko:', goldError);
      
      // Fallback to CoinGecko PAX Gold
      try {
        const proxyUrl = 'https://api.allorigins.win/raw?url=';
        const goldApiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=pax-gold&vs_currencies=usd&include_24hr_change=true';
        const goldResponse = await fetch(proxyUrl + encodeURIComponent(goldApiUrl));
        if (goldResponse.ok) {
          const goldData = await goldResponse.json();
          if (goldData['pax-gold'] && goldData['pax-gold'].usd) {
            const goldPrice = goldData['pax-gold'].usd;
            const goldChange = goldData['pax-gold'].usd_24h_change || 0;
            updateTickerItem('gold', goldPrice, goldChange);
          } else {
            throw new Error('Invalid PAX Gold data');
          }
        } else {
          throw new Error('CoinGecko gold API failed');
        }
      } catch (coingeckoError) {
        console.warn('All gold APIs failed, using cached price:', coingeckoError);
        // Use cached price if available
        const cachedGold = previousPrices.gold;
        if (cachedGold) {
          updateTickerItem('gold', cachedGold, 0);
        }
      }
    }
    
    // Fetch Oil price - using Yahoo Finance for WTI Crude (CL=F)
    try {
      const proxyUrl = 'https://api.allorigins.win/raw?url=';
      const oilUrl = 'https://query1.finance.yahoo.com/v8/finance/chart/CL=F?interval=1d&range=1d';
      const oilResponse = await fetch(proxyUrl + encodeURIComponent(oilUrl));
      
      if (oilResponse.ok) {
        const oilData = await oilResponse.json();
        if (oilData.chart && oilData.chart.result && oilData.chart.result[0]) {
          const result = oilData.chart.result[0];
          const oilPrice = result.meta.regularMarketPrice;
          const previousClose = result.meta.previousClose || oilPrice;
          const oilChange = oilPrice - previousClose;
          const oilChangePercent = previousClose ? ((oilChange / previousClose) * 100) : 0;
          updateTickerItem('oil', oilPrice, oilChangePercent);
        } else {
          throw new Error('Invalid oil data from Yahoo');
        }
      } else {
        throw new Error('Yahoo Finance oil API failed');
      }
    } catch (oilError) {
      console.warn('Yahoo Finance oil failed, trying Alpha Vantage:', oilError);
      
      // Fallback to Alpha Vantage
      try {
        const proxyUrl = 'https://api.allorigins.win/raw?url=';
        const oilApiUrl = 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=CL=F&apikey=demo';
        const oilResponse = await fetch(proxyUrl + encodeURIComponent(oilApiUrl));
        
        if (oilResponse.ok) {
          const oilData = await oilResponse.json();
          if (oilData['Global Quote'] && oilData['Global Quote']['05. price']) {
            const oilPrice = parseFloat(oilData['Global Quote']['05. price']);
            const oilChangePercent = parseFloat(oilData['Global Quote']['10. change percent'].replace('%', '')) || 0;
            updateTickerItem('oil', oilPrice, oilChangePercent);
          } else {
            throw new Error('Invalid oil data from Alpha Vantage');
          }
        } else {
          throw new Error('Alpha Vantage oil API failed');
        }
      } catch (alphaVantageError) {
        console.warn('All oil APIs failed, using cached price:', alphaVantageError);
        // Use cached price if available
        const cachedOil = previousPrices.oil;
        if (cachedOil) {
          updateTickerItem('oil', cachedOil, 0);
        }
      }
    }
  } catch (error) {
    console.error('Error fetching commodity prices:', error);
    // Use cached prices if available
    const previousPrices = JSON.parse(getUserItem('tickerPrices') || '{}');
    if (previousPrices.gold) {
      updateTickerItem('gold', previousPrices.gold, 0);
    }
    if (previousPrices.oil) {
      updateTickerItem('oil', previousPrices.oil, 0);
    }
  }
}

// Simulate custom tokens (BGC, SOLAR, WIND) with realistic real-time movements
function updateCustomTokens() {
  // Store previous prices for calculating change
  const previousPrices = JSON.parse(getUserItem('tickerPrices') || '{}');
  
  // BGC - simulate around $0.80-0.90 with small incremental changes
  const prevBgc = previousPrices.bgc || 0.86;
  const bgcChangePercent = (Math.random() * 1.0 - 0.5); // -0.5% to +0.5%
  const bgcPrice = prevBgc * (1 + bgcChangePercent / 100);
  const bgcChange = prevBgc ? ((bgcPrice - prevBgc) / prevBgc) * 100 : bgcChangePercent;
  updateTickerItem('bgc', bgcPrice, bgcChange);
  
  // SOLAR - simulate around $150-165 with small incremental changes
  const prevSolar = previousPrices.solar || 158;
  const solarChangePercent = (Math.random() * 0.8 - 0.4); // -0.4% to +0.4%
  const solarPrice = prevSolar * (1 + solarChangePercent / 100);
  const solarChange = prevSolar ? ((solarPrice - prevSolar) / prevSolar) * 100 : solarChangePercent;
  updateTickerItem('solar', solarPrice, solarChange);
  
  // WIND - simulate around $85-95 with small incremental changes
  const prevWind = previousPrices.wind || 89;
  const windChangePercent = (Math.random() * 0.8 - 0.4); // -0.4% to +0.4%
  const windPrice = prevWind * (1 + windChangePercent / 100);
  const windChange = prevWind ? ((windPrice - prevWind) / prevWind) * 100 : windChangePercent;
  updateTickerItem('wind', windPrice, windChange);
}

// Fetch all prices (global function)
async function fetchAllTickerPrices() {
  await Promise.all([
    fetchCryptoPrices(),
    fetchCommodityPrices(),
    Promise.resolve(updateCustomTokens())
  ]);
  
  // Save current prices for next update
  setUserItem('tickerPrices', JSON.stringify(marketTickerCurrentPrices));
}

// Initialize trading page with real crypto data and Buy/Sell functionality
function initTradingPage() {
  // Check if we're on the trading page
  const isTradingPage = window.location.pathname.includes('trading.html') || 
                        window.location.href.includes('trading.html') ||
                        document.querySelector('[data-tab-panel="crypto"]');
  
  if (!isTradingPage) return;
  
  // Initialize Buy/Sell buttons
  initTradeButtons();
  
  const tradingTable = document.querySelector('table tbody');
  if (!tradingTable) return;
  
  // Map of symbols to CoinGecko IDs
  const cryptoMap = {
    'BTC': 'bitcoin',
    'ETH': 'ethereum',
    'ADA': 'cardano',
    'SOL': 'solana'
  };
  
  async function updateCryptoPrices() {
    try {
      const proxyUrl = 'https://api.allorigins.win/raw?url=';
      const coinIds = Object.values(cryptoMap).join(',');
      const apiUrl = `https://api.coingecko.com/api/v3/simple/price?ids=${coinIds}&vs_currencies=usd&include_24hr_change=true`;
      
      const response = await fetch(proxyUrl + encodeURIComponent(apiUrl));
      if (!response.ok) throw new Error('Failed to fetch crypto prices');
      
      const data = await response.json();
      
      // Update each crypto in the trading table
      Object.keys(cryptoMap).forEach(symbol => {
        const coinId = cryptoMap[symbol];
        if (data[coinId]) {
          const price = data[coinId].usd;
          const change = data[coinId].usd_24h_change || 0;
          
          // Find all rows with this symbol
          const rows = Array.from(tradingTable.querySelectorAll('tr'));
          rows.forEach(row => {
            const symbolCell = row.querySelector('.font-semibold.text-gold-400');
            if (symbolCell && symbolCell.textContent.trim() === symbol) {
              // Update price
              const priceCell = row.querySelector('td.text-right.text-gold-400.font-semibold');
              if (priceCell) {
                priceCell.textContent = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
              }
              
              // Update 24h change
              const changeCell = row.querySelector('td.text-right.font-semibold');
              if (changeCell && (changeCell.classList.contains('text-green-400') || changeCell.classList.contains('text-red-400'))) {
                const isPositive = change >= 0;
                changeCell.textContent = `${isPositive ? '+' : ''}${change.toFixed(2)}%`;
                changeCell.className = `text-right py-4 font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
              }
            }
          });
        }
      });
    } catch (error) {
      console.error('Error fetching trading crypto prices, using fallback:', error);
      // Fallback to simulated data
      const symbols = Object.keys(cryptoMap);
      const basePrices = {
        'BTC': 43617,
        'ETH': 2567,
        'ADA': 0.52,
        'SOL': 98.5
      };
      
      symbols.forEach(symbol => {
        const basePrice = basePrices[symbol] || 100;
        const change = (Math.random() * 2 - 1); // -1% to +1%
        const price = basePrice * (1 + change / 100);
        
        // Find all rows with this symbol
        const rows = Array.from(tradingTable.querySelectorAll('tr'));
        rows.forEach(row => {
          const symbolCell = row.querySelector('.font-semibold.text-gold-400');
          if (symbolCell && symbolCell.textContent.trim() === symbol) {
            // Update price
            const priceCell = row.querySelector('td.text-right.text-gold-400.font-semibold');
            if (priceCell) {
              priceCell.textContent = `$${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
            
            // Update 24h change
            const changeCell = row.querySelector('td.text-right.font-semibold');
            if (changeCell && (changeCell.classList.contains('text-green-400') || changeCell.classList.contains('text-red-400'))) {
              const isPositive = change >= 0;
              changeCell.textContent = `${isPositive ? '+' : ''}${change.toFixed(2)}%`;
              changeCell.className = `text-right py-4 font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
            }
          }
        });
      });
    }
  }
  
  updateCryptoPrices();
  setInterval(updateCryptoPrices, 30000); // Update every 30 seconds to avoid rate limiting
}

// Initialize Buy/Sell trade buttons
function initTradeButtons() {
  const tradeButtons = document.querySelectorAll('[data-trade-action]');
  const tradeModal = document.getElementById('trade-modal');
  
  if (!tradeModal) return;
  
  tradeButtons.forEach(button => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      const action = button.getAttribute('data-trade-action'); // "buy" or "sell"
      const symbol = button.getAttribute('data-trade-symbol');
      const name = button.getAttribute('data-trade-name');
      
      // Get current price from the row or card
      let currentPrice = 0;
      const row = button.closest('tr');
      
      if (row) {
        // For crypto table rows
        const priceCell = row.querySelector('[data-crypto-price]') || row.querySelector('.text-right.text-gold-400.font-semibold');
        if (priceCell) {
          const priceText = priceCell.textContent.replace('$', '').replace(/,/g, '');
          currentPrice = parseFloat(priceText) || 0;
        }
      } else {
        // For stock cards - find the card container
        let card = button.closest('[class*="bg-black"]');
        if (!card) {
          // Try finding parent with specific classes
          let parent = button.parentElement;
          while (parent && parent !== document.body) {
            if (parent.classList.contains('bg-black') || parent.className.includes('bg-black')) {
              card = parent;
              break;
            }
            parent = parent.parentElement;
          }
        }
        
        if (card) {
          // Find price in the card
          const priceElement = card.querySelector('.text-xl.font-bold.text-gold-400') || 
                              card.querySelector('.text-2xl.font-bold.text-gold-400') ||
                              card.querySelector('p.text-xl.font-bold.text-gold-400');
          if (priceElement) {
            const priceText = priceElement.textContent.replace('$', '').replace(/,/g, '');
            currentPrice = parseFloat(priceText) || 0;
          }
        }
      }
      
      // Populate modal
      populateTradeModal(action, symbol, name, currentPrice);
      
      // Open modal
      tradeModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      
      // Show backdrop
      const backdrop = tradeModal.querySelector('[data-modal-backdrop]');
      if (backdrop) {
        backdrop.classList.remove('hidden');
      }
    });
  });
  
  // Handle amount input to calculate totals
  const amountInput = tradeModal.querySelector('[data-trade-amount-input]');
  if (amountInput) {
    amountInput.addEventListener('input', () => {
      calculateTradeTotals();
    });
  }
  
  // Handle trade submission
  const submitButton = tradeModal.querySelector('[data-trade-submit]');
  if (submitButton) {
    submitButton.addEventListener('click', () => {
      const amount = parseFloat(amountInput.value) || 0;
      const orderType = tradeModal.querySelector('[data-trade-order-type]').value;
      const action = submitButton.querySelector('[data-trade-submit-text]').textContent.toLowerCase();
      const symbol = submitButton.querySelector('[data-trade-symbol-text]').textContent;
      
      if (amount <= 0) {
        alert('Please enter a valid amount');
        return;
      }
      
      // If buying, deduct from current account
      if (action === 'buy') {
        // Get final amount (with fees)
        const finalAmountEl = tradeModal.querySelector('[data-trade-final-amount]');
        let finalAmount = amount;
        
        if (finalAmountEl) {
          const finalAmountText = finalAmountEl.textContent.replace('$', '').replace(/,/g, '');
          finalAmount = parseFloat(finalAmountText) || amount;
        } else {
          // Calculate final amount with 0.1% fee
          const fee = amount * 0.001;
          finalAmount = amount + fee;
        }
        
        // Get current account balance
        const accounts = loadBankAccounts();
        const currentAccount = accounts.find(acc => acc.accountType === 'current');
        
        if (!currentAccount) {
          alert('No current account found. Please set up a current account first.');
          return;
        }
        
        if (currentAccount.balance < finalAmount) {
          alert(`Insufficient balance. You need $${finalAmount.toFixed(2)} but only have $${currentAccount.balance.toFixed(2)}.`);
          return;
        }
        
        // Deduct from current account
        currentAccount.balance -= finalAmount;
        
        // Save accounts using user-specific storage
        const allAccounts = loadBankAccounts();
        const accountIndex = allAccounts.findIndex(acc => acc.id === currentAccount.id);
        if (accountIndex !== -1) {
          allAccounts[accountIndex] = currentAccount;
        } else {
          allAccounts.push(currentAccount);
        }
        
        // Save using the saveAccounts function from bank page
        const userId = getCurrentUserId();
        if (userId && typeof saveUserAccounts === 'function') {
          saveUserAccounts(userId, allAccounts);
        } else {
          // Fallback
          const userKey = getUserStorageKey('bankAccounts');
          localStorage.setItem(userKey, JSON.stringify(allAccounts));
        }
        
        // Update header balance
        if (window.updateHeaderBalance) {
          window.updateHeaderBalance();
        }
      }
      
      // Here you would normally send the trade to a backend
      console.log(`Executing ${action} order:`, {
        symbol,
        amount,
        orderType
      });
      
      // Show success message
      alert(`${action === 'buy' ? 'Buy' : 'Sell'} order for ${amount} ${symbol} submitted successfully!`);
      
      // Close modal
      closeTradeModal();
    });
  }
}

function populateTradeModal(action, symbol, name, currentPrice) {
  const modal = document.getElementById('trade-modal');
  if (!modal) return;
  
  // Update title
  const title = modal.querySelector('[data-trade-modal-title]');
  if (title) {
    title.textContent = `${action === 'buy' ? 'Buy' : 'Sell'} ${symbol}`;
  }
  
  // Update current price
  const priceElement = modal.querySelector('[data-trade-current-price]');
  if (priceElement) {
    // Format price based on its value (small prices like 0.845 need more decimals)
    let formattedPrice;
    if (currentPrice < 1) {
      formattedPrice = currentPrice.toLocaleString('en-US', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
    } else if (currentPrice < 100) {
      formattedPrice = currentPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } else {
      formattedPrice = currentPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    priceElement.textContent = `$${formattedPrice}`;
  }
  
  // Update amount label
  const amountLabel = modal.querySelector('[data-trade-amount-label]');
  if (amountLabel) {
    amountLabel.textContent = `Amount to ${action}`;
  }
  
  // Update submit button
  const submitText = modal.querySelector('[data-trade-submit-text]');
  const symbolText = modal.querySelector('[data-trade-symbol-text]');
  if (submitText) {
    submitText.textContent = action === 'buy' ? 'Buy' : 'Sell';
  }
  if (symbolText) {
    symbolText.textContent = symbol;
  }
  
  // Reset amount input
  const amountInput = modal.querySelector('[data-trade-amount-input]');
  if (amountInput) {
    amountInput.value = '';
  }
  
  // Reset totals
  const estimatedTotal = modal.querySelector('[data-trade-estimated-total]');
  const finalAmount = modal.querySelector('[data-trade-final-amount]');
  if (estimatedTotal) estimatedTotal.textContent = '$0.00';
  if (finalAmount) finalAmount.textContent = '$0.00';
  
  // Store current price for calculations
  modal.dataset.currentPrice = currentPrice.toString();
}

function calculateTradeTotals() {
  const modal = document.getElementById('trade-modal');
  if (!modal) return;
  
  const amountInput = modal.querySelector('[data-trade-amount-input]');
  const currentPrice = parseFloat(modal.dataset.currentPrice) || 0;
  const amount = parseFloat(amountInput.value) || 0;
  
  if (amount > 0 && currentPrice > 0) {
    const estimatedTotal = amount * currentPrice;
    const tradingFee = estimatedTotal * 0.001; // 0.1% fee
    const finalAmount = estimatedTotal + tradingFee;
    
    const estimatedTotalEl = modal.querySelector('[data-trade-estimated-total]');
    const finalAmountEl = modal.querySelector('[data-trade-final-amount]');
    
    if (estimatedTotalEl) {
      estimatedTotalEl.textContent = `$${estimatedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
    if (finalAmountEl) {
      finalAmountEl.textContent = `$${finalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
  } else {
    const estimatedTotalEl = modal.querySelector('[data-trade-estimated-total]');
    const finalAmountEl = modal.querySelector('[data-trade-final-amount]');
    if (estimatedTotalEl) estimatedTotalEl.textContent = '$0.00';
    if (finalAmountEl) finalAmountEl.textContent = '$0.00';
  }
}

function closeTradeModal() {
  const modal = document.getElementById('trade-modal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    const backdrop = modal.querySelector('[data-modal-backdrop]');
    if (backdrop) {
      backdrop.classList.add('hidden');
    }
    
    // Reset form
    const amountInput = modal.querySelector('[data-trade-amount-input]');
    if (amountInput) amountInput.value = '';
  }
}

// Add close handlers for trade modal
function initTradeModalClose() {
  const modal = document.getElementById('trade-modal');
  if (!modal) return;
  
  // Close button
  const closeButtons = modal.querySelectorAll('[data-modal-close="trade-modal"]');
  closeButtons.forEach(btn => {
    btn.addEventListener('click', closeTradeModal);
  });
  
  // Backdrop click
  const backdrop = modal.querySelector('[data-modal-backdrop="trade-modal"]');
  if (backdrop) {
    backdrop.addEventListener('click', closeTradeModal);
  }
  
  // ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      closeTradeModal();
    }
  });
}

// Initialize shares page with real stock data
function initSharesPage() {
  // Check if we're on the shares page
  const isSharesPage = window.location.pathname.includes('shares.html') || 
                       window.location.href.includes('shares.html') ||
                       document.querySelector('h1')?.textContent.includes('Company Shares');
  
  if (!isSharesPage) return;
  
  // Stock symbols to fetch
  const stockSymbols = ['TSLA', 'AAPL', 'SHEL', 'MSFT', 'NVDA', 'XOM'];
  
  async function updateStockPrices() {
    try {
      // Use Yahoo Finance as primary source (better for frequent updates, no strict rate limits)
      await fetchStocksFromYahoo();
    } catch (error) {
      console.error('Error fetching stock prices:', error);
      // Try Finnhub as fallback
      try {
        const finnhubApiKey = 'demo';
        const promises = stockSymbols.map(async (symbol) => {
          try {
            const response = await fetch(`https://finnhub.io/api/v1/quote?symbol=${symbol}&token=${finnhubApiKey}`);
            if (response.ok) {
              const data = await response.json();
              if (data.c && data.c > 0) {
                const price = data.c;
                const previousPrice = data.pc || price;
                const change = price - previousPrice;
                const changePercent = previousPrice ? ((change / previousPrice) * 100) : 0;
                
                const previousPrices = JSON.parse(getUserItem('stockPrices') || '{}');
                previousPrices[symbol] = price;
                setUserItem('stockPrices', JSON.stringify(previousPrices));
                
                updateStockInPage(symbol, price, change, changePercent);
                return { symbol, success: true };
              }
            }
          } catch (e) {
            console.warn(`Failed to fetch ${symbol} from Finnhub:`, e);
          }
          return { symbol, success: false };
        });
        await Promise.all(promises);
      } catch (finnhubError) {
        console.warn('All APIs failed, using cached prices:', finnhubError);
        // Use cached prices if all APIs fail
        const previousPrices = JSON.parse(getUserItem('stockPrices') || '{}');
        stockSymbols.forEach(symbol => {
          const cachedPrice = previousPrices[symbol];
          if (cachedPrice) {
            updateStockInPage(symbol, cachedPrice, 0, 0);
          }
        });
      }
    }
  }
  
  async function fetchStocksFromYahoo() {
    try {
      const proxyUrl = 'https://api.allorigins.win/raw?url=';
      const symbols = stockSymbols.join(',');
      // Yahoo Finance API (no key needed)
      const yahooUrl = `https://query1.finance.yahoo.com/v8/finance/chart/${symbols}?interval=1d&range=1d`;
      
      const response = await fetch(proxyUrl + encodeURIComponent(yahooUrl));
      if (!response.ok) throw new Error('Yahoo Finance API failed');
      
      const data = await response.json();
      
      if (data.chart && data.chart.result) {
        const previousPrices = JSON.parse(getUserItem('stockPrices') || '{}');
        
        data.chart.result.forEach((result, index) => {
          const symbol = result.meta.symbol;
          const price = result.meta.regularMarketPrice;
          const previousClose = result.meta.previousClose || price;
          const change = price - previousClose;
          const changePercent = previousClose ? ((change / previousClose) * 100) : 0;
          
          if (symbol && price) {
            previousPrices[symbol] = price;
            setUserItem('stockPrices', JSON.stringify(previousPrices));
            
            updateStockInPage(symbol, price, change, changePercent);
          }
        });
      }
    } catch (yahooError) {
      console.error('Yahoo Finance API failed:', yahooError);
      throw yahooError;
    }
  }
  
  function updateStockInPage(symbol, price, change, changePercent) {
    const isPositive = change >= 0;
    
    // Update portfolio table
    const portfolioRows = document.querySelectorAll('table tbody tr');
    portfolioRows.forEach(row => {
      const symbolCell = row.querySelector('.font-semibold.text-gold-400');
      if (symbolCell && symbolCell.textContent.trim() === symbol) {
        // Update current value (assuming shares are fixed)
        const sharesCell = row.querySelectorAll('td.text-right.text-gold-400.font-semibold')[0];
        const avgPriceCell = row.querySelectorAll('td.text-right.text-gold-600')[0];
        if (sharesCell && avgPriceCell) {
          const shares = parseInt(sharesCell.textContent) || 0;
          const avgPrice = parseFloat(avgPriceCell.textContent.replace('$', '')) || price;
          const currentValue = shares * price;
          const pnl = (price - avgPrice) * shares;
          const pnlPercent = ((price - avgPrice) / avgPrice) * 100;
          
          // Update current value
          const valueCell = row.querySelectorAll('td.text-right.text-gold-400.font-semibold')[1];
          if (valueCell) {
            valueCell.textContent = `$${currentValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
          }
          
          // Update P&L
          const pnlCell = row.querySelectorAll('td.text-right.font-semibold')[1];
          if (pnlCell) {
            pnlCell.textContent = `${isPositive ? '+' : ''}$${Math.abs(pnl).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${isPositive ? '+' : ''}${pnlPercent.toFixed(2)}%)`;
            pnlCell.className = `text-right py-4 font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
          }
        }
      }
    });
    
    // Update available stocks cards - try multiple selectors to find cards
    let stockCards = document.querySelectorAll('.bg-black\\/60.backdrop-blur-sm.border');
    if (stockCards.length === 0) {
      // Fallback: try finding cards by other class combinations
      stockCards = document.querySelectorAll('[class*="bg-black"][class*="backdrop-blur-sm"][class*="border"]');
    }
    if (stockCards.length === 0) {
      // Another fallback: find all cards with stock symbols
      const symbolElements = document.querySelectorAll('h3.text-lg.font-semibold.text-gold-400');
      const cardArray = [];
      symbolElements.forEach(h3 => {
        const card = h3.closest('[class*="bg-black"]');
        if (card) cardArray.push(card);
      });
      stockCards = cardArray;
    }
    
    stockCards.forEach(card => {
      if (!card) return;
      // Find the symbol - could be in h3 or other element
      const cardSymbol = card.querySelector('h3.text-lg.font-semibold.text-gold-400') || 
                        card.querySelector('.text-lg.font-semibold.text-gold-400');
      if (cardSymbol && cardSymbol.textContent.trim() === symbol) {
        // Update price - look for the price span in the mb-4 div
        const priceContainer = card.querySelector('.mb-4 .flex.justify-between');
        if (priceContainer) {
          const priceSpan = priceContainer.querySelector('.text-2xl.font-bold.text-gold-400');
          if (priceSpan) {
            priceSpan.textContent = `$${price.toFixed(2)}`;
          }
          
          // Update change - find the change container and inner span
          const changeContainer = priceContainer.querySelector('.flex.items-center.space-x-1');
          if (changeContainer) {
            const changeSpan = changeContainer.querySelector('span:last-child');
            if (changeSpan) {
              const changeValue = Math.abs(change);
              changeSpan.textContent = `${isPositive ? '+' : '-'}$${changeValue.toFixed(2)} (${isPositive ? '+' : ''}${changePercent.toFixed(2)}%)`;
            }
            
            // Update container color
            changeContainer.className = `flex items-center space-x-1 text-sm font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
            
            // Update icon
            const icon = changeContainer.querySelector('svg');
            if (icon) {
              if (isPositive) {
                icon.innerHTML = '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline>';
                icon.style.transform = '';
              } else {
                icon.innerHTML = '<polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline><polyline points="16 17 22 17 22 11"></polyline>';
                icon.style.transform = 'rotate(180deg)';
              }
            }
          }
        } else {
          // Fallback: try to find price and change elements directly
          const priceSpan = card.querySelector('.text-2xl.font-bold.text-gold-400');
          if (priceSpan) {
            priceSpan.textContent = `$${price.toFixed(2)}`;
          }
          
          const changeSpan = card.querySelector('.flex.items-center.space-x-1 span:last-child');
          if (changeSpan) {
            const changeValue = Math.abs(change);
            changeSpan.textContent = `${isPositive ? '+' : '-'}$${changeValue.toFixed(2)} (${isPositive ? '+' : ''}${changePercent.toFixed(2)}%)`;
            const changeContainer = changeSpan.closest('.flex.items-center.space-x-1');
            if (changeContainer) {
              changeContainer.className = `flex items-center space-x-1 text-sm font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
              const icon = changeContainer.querySelector('svg');
              if (icon) {
                if (isPositive) {
                  icon.innerHTML = '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline>';
                  icon.style.transform = '';
                } else {
                  icon.innerHTML = '<polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline><polyline points="16 17 22 17 22 11"></polyline>';
                  icon.style.transform = 'rotate(180deg)';
                }
              }
            }
          }
        }
        
        // Update data attributes for modals and buttons
        const tradeButtons = card.querySelectorAll('[data-trade-data]');
        tradeButtons.forEach(btn => {
          try {
            const data = JSON.parse(btn.getAttribute('data-trade-data'));
            if (data.symbol === symbol) {
              data.price = `$${price.toFixed(2)}`;
              data.change = `${isPositive ? '+' : ''}$${Math.abs(change).toFixed(2)} (${isPositive ? '+' : ''}${changePercent.toFixed(2)}%)`;
              btn.setAttribute('data-trade-data', JSON.stringify(data));
            }
          } catch (e) {
            console.error('Error updating trade data:', e);
          }
        });
        
        // Update stock modal button attributes
        const stockModalButtons = card.querySelectorAll('[data-stock-modal-open]');
        stockModalButtons.forEach(btn => {
          if (btn.getAttribute('data-stock-symbol') === symbol) {
            btn.setAttribute('data-stock-price', price.toFixed(2));
            btn.setAttribute('data-stock-change', change.toFixed(2));
            btn.setAttribute('data-stock-change-percent', changePercent.toFixed(2));
          }
        });
      }
    });
  }
  
  // Initial update
  updateStockPrices();
  // Update every 3 seconds with real market data
  setInterval(updateStockPrices, 3000);
}

// Initialize watchlist functionality
function initWatchlist() {
  // Load watchlist from user storage
  let watchlist = JSON.parse(getUserItem('watchlist') || '["TSLA", "AAPL"]');
  
  // Get watchlist state
  function getWatchlist() {
    return JSON.parse(getUserItem('watchlist') || '["TSLA", "AAPL"]');
  }
  
  // Update watchlist
  function updateWatchlist(symbol, add) {
    let current = getWatchlist();
    if (add) {
      if (!current.includes(symbol)) {
        current.push(symbol);
      }
    } else {
      current = current.filter(s => s !== symbol);
    }
    setUserItem('watchlist', JSON.stringify(current));
    return current;
  }
  
  // Update watchlist buttons based on saved state
  function updateWatchlistButtons() {
    const currentWatchlist = getWatchlist();
    document.querySelectorAll('[data-watchlist-toggle]').forEach(button => {
      const symbol = button.getAttribute('data-watchlist-toggle');
      const isInWatchlist = currentWatchlist.includes(symbol);
      const svg = button.querySelector('svg');
      
      if (isInWatchlist) {
        button.classList.remove('text-gold-600');
        button.classList.add('text-gold-400', 'bg-gold-600/20');
        if (svg) {
          svg.setAttribute('fill', 'currentColor');
        }
      } else {
        button.classList.remove('text-gold-400', 'bg-gold-600/20');
        button.classList.add('text-gold-600');
        if (svg) {
          svg.setAttribute('fill', 'none');
        }
      }
    });
    
    // Update watchlist count in stat card
    const watchlistCards = document.querySelectorAll('.text-2xl.font-bold.text-blue-400');
    watchlistCards.forEach(countEl => {
      const parent = countEl.parentElement;
      if (parent && parent.textContent.includes('Watchlist')) {
        countEl.textContent = currentWatchlist.length;
      }
    });
    
    // Update modal watchlist button if modal is open
    const modalWatchlistBtn = document.querySelector('[data-stock-watchlist-toggle]');
    const modalWatchlistText = document.querySelector('[data-stock-watchlist-text]');
    if (modalWatchlistBtn && modalWatchlistText) {
      const modalSymbol = document.querySelector('[data-stock-modal-symbol]')?.textContent;
      if (modalSymbol) {
        const isInWatchlist = currentWatchlist.includes(modalSymbol);
        modalWatchlistText.textContent = isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist';
      }
    }
  }
  
  // Add click handlers to watchlist buttons
  document.querySelectorAll('[data-watchlist-toggle]').forEach(button => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const symbol = button.getAttribute('data-watchlist-toggle');
      const currentWatchlist = getWatchlist();
      const isInWatchlist = currentWatchlist.includes(symbol);
      
      updateWatchlist(symbol, !isInWatchlist);
      updateWatchlistButtons();
    });
  });
  
  // Handle modal watchlist toggle - this will be set up in initStockModal
  window.updateWatchlistFromModal = function(symbol) {
    const currentWatchlist = getWatchlist();
    const isInWatchlist = currentWatchlist.includes(symbol);
    updateWatchlist(symbol, !isInWatchlist);
    updateWatchlistButtons();
  };
  
  // Initial update
  updateWatchlistButtons();
  
  // Expose update function globally for modal to use
  window.updateWatchlistButtons = updateWatchlistButtons;
}

// Initialize stock modal functionality
function initStockModal() {
  const stockModal = document.getElementById('stock-modal');
  if (!stockModal) return;
  
  // Sector color mapping
  const sectorColors = {
    'Technology': 'text-blue-400 bg-blue-600/20',
    'Energy': 'text-orange-400 bg-orange-600/20',
    'Automotive': 'text-green-400 bg-green-600/20'
  };
  
  // Open stock modal
  document.querySelectorAll('[data-stock-modal-open]').forEach(button => {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      
      const symbol = button.getAttribute('data-stock-symbol');
      const name = button.getAttribute('data-stock-name');
      // Get latest real-time price from stored prices (updated every 3 seconds)
      const storedPrices = JSON.parse(getUserItem('stockPrices') || '{}');
      const latestPrice = storedPrices[symbol] || parseFloat(button.getAttribute('data-stock-price')) || 0;
      const buttonPrice = parseFloat(button.getAttribute('data-stock-price')) || latestPrice;
      const buttonChange = parseFloat(button.getAttribute('data-stock-change')) || 0;
      const buttonChangePercent = parseFloat(button.getAttribute('data-stock-change-percent')) || 0;
      
      // Use latest price if available, otherwise use button attributes
      const price = latestPrice || buttonPrice;
      const previousPrice = storedPrices[symbol] ? (buttonPrice || latestPrice) : buttonPrice;
      const change = price - previousPrice;
      const changePercent = previousPrice ? ((change / previousPrice) * 100) : buttonChangePercent;
      
      const marketCap = button.getAttribute('data-stock-market-cap');
      const volume = button.getAttribute('data-stock-volume');
      const sector = button.getAttribute('data-stock-sector');
      const description = button.getAttribute('data-stock-description');
      const logo = button.getAttribute('data-stock-logo');
      
      // Populate modal
      const title = stockModal.querySelector('[data-stock-modal-title]');
      const logoEl = stockModal.querySelector('[data-stock-modal-logo]');
      const symbolEl = stockModal.querySelector('[data-stock-modal-symbol]');
      const nameEl = stockModal.querySelector('[data-stock-modal-name]');
      const sectorEl = stockModal.querySelector('[data-stock-modal-sector]');
      const priceEl = stockModal.querySelector('[data-stock-modal-price]');
      const changeEl = stockModal.querySelector('[data-stock-modal-change]');
      const descriptionEl = stockModal.querySelector('[data-stock-modal-description]');
      const marketCapEl = stockModal.querySelector('[data-stock-modal-market-cap]');
      const volumeEl = stockModal.querySelector('[data-stock-modal-volume]');
      const watchlistText = stockModal.querySelector('[data-stock-watchlist-text]');
      
      if (title) title.textContent = `${symbol} - ${name}`;
      if (logoEl) logoEl.textContent = logo;
      if (symbolEl) symbolEl.textContent = symbol;
      if (nameEl) nameEl.textContent = name;
      if (sectorEl) {
        sectorEl.textContent = sector;
        sectorEl.className = `inline-block px-2 py-1 rounded text-xs font-medium mt-2 ${sectorColors[sector] || 'text-gold-400 bg-gold-600/20'}`;
      }
      if (priceEl) priceEl.textContent = `$${price.toFixed(2)}`;
      if (changeEl) {
        const changeSign = change >= 0 ? '+' : '';
        const percentSign = changePercent >= 0 ? '+' : '';
        changeEl.textContent = `${changeSign}$${Math.abs(change).toFixed(2)} (${percentSign}${changePercent.toFixed(2)}%)`;
        changeEl.className = `font-semibold ${change >= 0 ? 'text-green-400' : 'text-red-400'}`;
      }
      if (descriptionEl) descriptionEl.textContent = description;
      if (marketCapEl) marketCapEl.textContent = marketCap;
      if (volumeEl) volumeEl.textContent = volume;
      
      // Update watchlist button text
      const watchlist = JSON.parse(getUserItem('watchlist') || '["TSLA", "AAPL"]');
      if (watchlistText) {
        watchlistText.textContent = watchlist.includes(symbol) ? 'Remove from Watchlist' : 'Add to Watchlist';
      }
      
      // Store current symbol for watchlist toggle
      stockModal.dataset.currentSymbol = symbol;
      
      // Update watchlist buttons on page (in case modal was opened from card)
      if (window.updateWatchlistButtons) {
        // This will be called from initWatchlist
      }
      
      // Open modal
      stockModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      
      const backdrop = stockModal.querySelector('[data-modal-backdrop="stock-modal"]');
      if (backdrop) {
        backdrop.classList.remove('hidden');
      }
    });
  });
  
  // Handle modal watchlist toggle
  const modalWatchlistBtn = stockModal.querySelector('[data-stock-watchlist-toggle]');
  if (modalWatchlistBtn) {
    modalWatchlistBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const symbol = stockModal.dataset.currentSymbol;
      if (symbol && window.updateWatchlistFromModal) {
        // Get current watchlist state
        const watchlist = JSON.parse(getUserItem('watchlist') || '["TSLA", "AAPL"]');
        const isInWatchlist = watchlist.includes(symbol);
        
        // Toggle watchlist
        window.updateWatchlistFromModal(symbol);
        
        // Update button text immediately
        const watchlistText = stockModal.querySelector('[data-stock-watchlist-text]');
        if (watchlistText) {
          // Toggle the text based on the new state (opposite of current)
          watchlistText.textContent = isInWatchlist ? 'Add to Watchlist' : 'Remove from Watchlist';
        }
        
        // Also update all watchlist buttons on the page
        if (window.updateWatchlistButtons) {
          window.updateWatchlistButtons();
        }
      }
    });
  }
  
  // Handle Buy/Sell buttons
  const buyBtn = stockModal.querySelector('[data-stock-buy]');
  const sellBtn = stockModal.querySelector('[data-stock-sell]');
  const sharesInput = stockModal.querySelector('[data-stock-shares-input]');
  
  // Stop propagation on input to prevent modal from closing
  if (sharesInput) {
    sharesInput.addEventListener('click', (e) => {
      e.stopPropagation();
    });
    sharesInput.addEventListener('focus', (e) => {
      e.stopPropagation();
    });
  }
  
  if (buyBtn) {
    buyBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const shares = parseInt(sharesInput.value) || 0;
      const symbol = stockModal.dataset.currentSymbol;
      
      if (shares <= 0) {
        alert('Please enter a valid number of shares');
        return;
      }
      
      // Get current price from modal
      const priceText = stockModal.querySelector('[data-stock-modal-price]')?.textContent;
      if (!priceText) {
        alert('Unable to get current stock price');
        return;
      }
      
      const currentPrice = parseFloat(priceText.replace('$', '').replace(/,/g, '')) || 0;
      if (currentPrice <= 0) {
        alert('Invalid stock price');
        return;
      }
      
      // Calculate total cost: shares × price
      const totalCost = shares * currentPrice;
      const tradingFee = totalCost * 0.001; // 0.1% fee
      const finalAmount = totalCost + tradingFee;
      
      // Get current account balance
      const accounts = loadBankAccounts();
      const currentAccount = accounts.find(acc => acc.accountType === 'current');
      
      if (!currentAccount) {
        alert('No current account found. Please set up a current account first.');
        return;
      }
      
      if (currentAccount.balance < finalAmount) {
        alert(`Insufficient balance. You need $${finalAmount.toFixed(2)} but only have $${currentAccount.balance.toFixed(2)}.`);
        return;
      }
      
      // Deduct from current account
      currentAccount.balance -= finalAmount;
      
      // Save accounts using user-specific storage
      const allAccounts = loadBankAccounts();
      const accountIndex = allAccounts.findIndex(acc => acc.id === currentAccount.id);
      if (accountIndex !== -1) {
        allAccounts[accountIndex] = currentAccount;
      } else {
        allAccounts.push(currentAccount);
      }
      
      // Save using the saveAccounts function from bank page
      const userId = getCurrentUserId();
      if (userId && typeof saveUserAccounts === 'function') {
        saveUserAccounts(userId, allAccounts);
      } else {
        // Fallback
        const userKey = getUserStorageKey('bankAccounts');
        localStorage.setItem(userKey, JSON.stringify(allAccounts));
      }
      
      // Update header balance
      if (window.updateHeaderBalance) {
        window.updateHeaderBalance();
      }
      
      alert(`Successfully bought ${shares} shares of ${symbol} for $${totalCost.toFixed(2)} (including $${tradingFee.toFixed(2)} fee)`);
      closeStockModal();
    });
  }
  
  if (sellBtn) {
    sellBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const shares = parseInt(sharesInput.value) || 0;
      const symbol = stockModal.dataset.currentSymbol;
      
      if (shares <= 0) {
        alert('Please enter a valid number of shares');
        return;
      }
      
      // Get current price from modal
      const priceText = stockModal.querySelector('[data-stock-modal-price]')?.textContent;
      if (!priceText) {
        alert('Unable to get current stock price');
        return;
      }
      
      const currentPrice = parseFloat(priceText.replace('$', '').replace(/,/g, '')) || 0;
      if (currentPrice <= 0) {
        alert('Invalid stock price');
        return;
      }
      
      // Calculate total proceeds: shares × price
      const totalProceeds = shares * currentPrice;
      const tradingFee = totalProceeds * 0.001; // 0.1% fee
      const finalAmount = totalProceeds - tradingFee;
      
      // Get current account balance
      const accounts = loadBankAccounts();
      const currentAccount = accounts.find(acc => acc.accountType === 'current');
      
      if (!currentAccount) {
        alert('No current account found. Please set up a current account first.');
        return;
      }
      
      // Add to current account
      currentAccount.balance += finalAmount;
      
      // Save accounts using user-specific storage
      const allAccounts = loadBankAccounts();
      const accountIndex = allAccounts.findIndex(acc => acc.id === currentAccount.id);
      if (accountIndex !== -1) {
        allAccounts[accountIndex] = currentAccount;
      } else {
        allAccounts.push(currentAccount);
      }
      
      // Save using the saveAccounts function from bank page
      const userId = getCurrentUserId();
      if (userId && typeof saveUserAccounts === 'function') {
        saveUserAccounts(userId, allAccounts);
      } else {
        // Fallback
        const userKey = getUserStorageKey('bankAccounts');
        localStorage.setItem(userKey, JSON.stringify(allAccounts));
      }
      
      // Update header balance
      if (window.updateHeaderBalance) {
        window.updateHeaderBalance();
      }
      
      alert(`Successfully sold ${shares} shares of ${symbol} for $${totalProceeds.toFixed(2)} (after $${tradingFee.toFixed(2)} fee, you received $${finalAmount.toFixed(2)})`);
      closeStockModal();
    });
  }
  
  // Stop propagation on modal content to prevent closing when clicking inside
  // Find the content div (first child div with rounded corners)
  const modalContent = stockModal.querySelector('div.rounded-xl');
  if (modalContent) {
    modalContent.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }
  
  // Close handlers
  const closeButtons = stockModal.querySelectorAll('[data-modal-close="stock-modal"]');
  closeButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      closeStockModal();
    });
  });
  
  // Close on backdrop click (only when clicking the modal container itself, not content)
  stockModal.addEventListener('click', (e) => {
    if (e.target === stockModal) {
      closeStockModal();
    }
  });
  
  const backdrop = stockModal.querySelector('[data-modal-backdrop="stock-modal"]');
  if (backdrop) {
    backdrop.addEventListener('click', (e) => {
      e.stopPropagation();
      closeStockModal();
    });
  }
  
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !stockModal.classList.contains('hidden')) {
      closeStockModal();
    }
  });
}

function closeStockModal() {
  const stockModal = document.getElementById('stock-modal');
  if (stockModal) {
    stockModal.classList.add('hidden');
    document.body.style.overflow = '';
    
    const backdrop = stockModal.querySelector('[data-modal-backdrop="stock-modal"]');
    if (backdrop) {
      backdrop.classList.add('hidden');
    }
    
    // Reset input
    const sharesInput = stockModal.querySelector('[data-stock-shares-input]');
    if (sharesInput) sharesInput.value = '';
  }
}

// Check if user is authenticated - NON-BLOCKING to prevent navigation loops
let authChecked = false;
let redirecting = false;

function checkAuthentication() {
  // Prevent multiple checks and redirects
  if (authChecked || redirecting) {
    return true;
  }
  
  // Check if we're on login page - if so, skip check
  const currentPath = window.location.pathname;
  const currentHref = window.location.href;
  const isLoginPage = currentPath.includes('login.html') || 
                     currentHref.includes('login.html');
  
  if (isLoginPage) {
    authChecked = true;
    return true; // Allow login page to load
  }
  
  // Check for user ID - but don't block navigation
  try {
    const currentUserId = localStorage.getItem('currentUserId');
    if (!currentUserId || currentUserId === 'null' || currentUserId === 'undefined' || currentUserId.trim() === '') {
      // User not authenticated - redirect to login ONCE with delay to prevent loops
      if (!redirecting) {
        redirecting = true;
        authChecked = true;
        // Use setTimeout to prevent blocking navigation
        setTimeout(() => {
          if (window.location.pathname.includes('login.html')) return;
          window.location.replace('login.html');
        }, 100);
      }
      return false;
    }
    authChecked = true;
    return true; // User is authenticated
  } catch (e) {
    // If localStorage is not available, allow navigation
    authChecked = true;
    return true;
  }
}

// Get current user ID
function getCurrentUserId() {
  return localStorage.getItem('currentUserId');
}

// Global function to load accounts from user-specific storage
function loadBankAccounts() {
  const userId = getCurrentUserId();
  if (!userId) return [];
  
  // Use auth.js functions if available, otherwise fallback
  if (typeof getUserAccounts === 'function') {
    return getUserAccounts(userId);
  }
  
  // Fallback to old localStorage method for backward compatibility
  const savedAccounts = localStorage.getItem('bankAccounts');
  if (savedAccounts) {
    return JSON.parse(savedAccounts);
  }
  return [];
}

// Global function to update header balance on all pages
function updateHeaderBalance() {
  const headerBalance = document.querySelector('[data-header-balance]');
  if (headerBalance) {
    const accounts = loadBankAccounts();
    const currentAccounts = accounts.filter(acc => acc.accountType === 'current');
    const totalBalance = currentAccounts.reduce((sum, acc) => sum + acc.balance, 0);
    
    headerBalance.textContent = `$${totalBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }
  
  // Also update profile page account balance (sum of ALL accounts)
  const profileBalance = document.querySelector('[data-profile-balance]');
  if (profileBalance) {
    const accounts = loadBankAccounts();
    const totalBalance = accounts.reduce((sum, acc) => sum + acc.balance, 0);
    
    profileBalance.textContent = `$${totalBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }
  
  // Update portfolio section Total Balance (sum of ALL accounts)
  const portfolioBalance = document.querySelector('[data-portfolio-balance]');
  if (portfolioBalance) {
    const accounts = loadBankAccounts();
    const totalBalance = accounts.reduce((sum, acc) => sum + acc.balance, 0);
    
    portfolioBalance.textContent = `$${totalBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  }
}

// Make it globally accessible
window.updateHeaderBalance = updateHeaderBalance;

// Bank page functionality
function initBankPage() {
  const accountsGrid = document.getElementById('bank-accounts-grid');
  const addAccountModal = document.getElementById('add-account-modal');
  const addAccountSubmit = document.getElementById('add-account-submit');
  
  if (!accountsGrid) return;
  
  // Load accounts from localStorage or use default accounts
  function loadAccounts() {
    return loadBankAccounts();
  }
  
  // Save accounts to user-specific storage
  function saveAccounts(accounts) {
    const userId = getCurrentUserId();
    if (!userId) return;
    
    // Use auth.js functions if available
    if (typeof saveUserAccounts === 'function') {
      saveUserAccounts(userId, accounts);
    } else {
      // Fallback to old localStorage method
      localStorage.setItem('bankAccounts', JSON.stringify(accounts));
    }
    // Update header balance on all pages after saving
    updateHeaderBalance();
  }
  
  // Get account type display name
  function getAccountTypeDisplay(type) {
    const types = {
      'current': 'Current Account',
      'savings': 'Savings Account',
      'business': 'Business Account'
    };
    return types[type] || 'Account';
  }
  
  // Get account icon emoji
  function getAccountIcon(type) {
    const icons = {
      'current': '🏦',
      'savings': '💳',
      'business': '🏢'
    };
    return icons[type] || '🏦';
  }
  
  // Render a single account card
  function renderAccountCard(account) {
    const card = document.createElement('div');
    card.className = 'bg-black/60 backdrop-blur-sm border border-gold-600/30 rounded-xl p-6 hover:bg-black/70 hover:border-gold-500/50 transition-all duration-300 shadow-lg shadow-gold-600/20 cursor-pointer';
    card.setAttribute('data-account-id', account.id);
    
    card.innerHTML = `
      <div class="flex items-center space-x-4 mb-4">
        <div class="text-3xl">${getAccountIcon(account.accountType)}</div>
        <div>
          <h3 class="text-lg font-semibold text-gold-400">${account.bankName}</h3>
          <p class="text-sm text-gold-600">${getAccountTypeDisplay(account.accountType)}</p>
        </div>
      </div>
      <div class="space-y-2">
        <div class="flex justify-between">
          <span class="text-gold-600">IBAN:</span>
          <span class="text-gold-400 font-mono text-sm">${account.iban}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gold-600">Balance:</span>
          <span class="text-gold-400 font-semibold" data-account-balance="${account.id}">$${account.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
        </div>
      </div>
    `;
    
    // Add click handler to open deposit modal
    card.addEventListener('click', () => {
      openDepositModal(account);
    });
    
    return card;
  }
  
  // Render all accounts
  function renderAccounts() {
    const accounts = loadAccounts();
    accountsGrid.innerHTML = '';
    accounts.forEach(account => {
      const card = renderAccountCard(account);
      accountsGrid.appendChild(card);
    });
    // Update header balance after rendering
    updateHeaderBalance();
  }
  
  // Open deposit modal with account data
  function openDepositModal(account) {
    const depositModal = document.getElementById('deposit-account-modal');
    const bankNameEl = document.getElementById('deposit-bank-name');
    const currentBalanceEl = document.getElementById('deposit-current-balance');
    const amountInput = document.getElementById('deposit-amount');
    const accountTypeSelect = document.getElementById('deposit-account-type');
    const cardNameInput = document.getElementById('deposit-card-name');
    const cardNumberInput = document.getElementById('deposit-card-number');
    const cardDateInput = document.getElementById('deposit-card-date');
    const cardCVCInput = document.getElementById('deposit-card-cvc');
    
    if (!depositModal || !bankNameEl || !currentBalanceEl || !amountInput || !accountTypeSelect) return;
    
    // Populate modal with account data
    bankNameEl.textContent = account.bankName;
    currentBalanceEl.textContent = `$${account.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    amountInput.value = '';
    accountTypeSelect.value = account.accountType;
    
    // Reset card inputs
    if (cardNameInput) cardNameInput.value = '';
    if (cardNumberInput) cardNumberInput.value = '';
    if (cardDateInput) cardDateInput.value = '';
    if (cardCVCInput) cardCVCInput.value = '';
    
    // Store current account ID in modal for deposit
    depositModal.dataset.accountId = account.id;
    
    // Open modal
    depositModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  
  // Format card number input (XXXX-XXXX-XXXX-XXXX)
  function formatCardNumber(input) {
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    if (value.length > 16) value = value.slice(0, 16); // Limit to 16 digits
    
    // Add dashes after every 4 digits
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
      if (i > 0 && i % 4 === 0) {
        formatted += '-';
      }
      formatted += value[i];
    }
    
    input.value = formatted;
  }
  
  // Format expiry date input (MM/YY)
  function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    if (value.length > 4) value = value.slice(0, 4); // Limit to 4 digits
    
    // Add slash after 2 digits
    if (value.length >= 2) {
      input.value = value.slice(0, 2) + '/' + value.slice(2);
    } else {
      input.value = value;
    }
  }
  
  // Format CVC input (only digits, max 3)
  function formatCVC(input) {
    input.value = input.value.replace(/\D/g, '').slice(0, 3);
  }
  
  // Handle deposit submission
  function initDepositModal() {
    const depositModal = document.getElementById('deposit-account-modal');
    const depositSubmit = document.getElementById('deposit-submit');
    const deleteSubmit = document.getElementById('delete-account-submit');
    const amountInput = document.getElementById('deposit-amount');
    const accountTypeSelect = document.getElementById('deposit-account-type');
    const cardNumberInput = document.getElementById('deposit-card-number');
    const cardDateInput = document.getElementById('deposit-card-date');
    const cardCVCInput = document.getElementById('deposit-card-cvc');
    
    if (!depositModal || !depositSubmit || !amountInput || !accountTypeSelect) return;
    
    // Format card number input
    if (cardNumberInput) {
      cardNumberInput.addEventListener('input', (e) => {
        formatCardNumber(e.target);
      });
      cardNumberInput.addEventListener('keypress', (e) => {
        // Only allow digits
        if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
          e.preventDefault();
        }
      });
    }
    
    // Format expiry date input
    if (cardDateInput) {
      cardDateInput.addEventListener('input', (e) => {
        formatExpiryDate(e.target);
      });
      cardDateInput.addEventListener('keypress', (e) => {
        // Only allow digits
        if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
          e.preventDefault();
        }
      });
    }
    
    // Format CVC input
    if (cardCVCInput) {
      cardCVCInput.addEventListener('input', (e) => {
        formatCVC(e.target);
      });
      cardCVCInput.addEventListener('keypress', (e) => {
        // Only allow digits
        if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
          e.preventDefault();
        }
      });
    }
    
    // Handle deposit
    depositSubmit.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      
      const accountId = parseInt(depositModal.dataset.accountId);
      const depositAmount = parseFloat(amountInput.value);
      
      // Validate card inputs
      const cardName = document.getElementById('deposit-card-name')?.value.trim();
      const cardNumber = cardNumberInput?.value.replace(/-/g, '');
      const cardDate = cardDateInput?.value;
      const cardCVC = cardCVCInput?.value;
      
      if (!cardName) {
        alert('Please enter the name on the card');
        return;
      }
      
      if (!cardNumber || cardNumber.length !== 16) {
        alert('Please enter a valid 16-digit card number');
        return;
      }
      
      if (!cardDate || !/^\d{2}\/\d{2}$/.test(cardDate)) {
        alert('Please enter a valid expiry date (MM/YY)');
        return;
      }
      
      if (!cardCVC || cardCVC.length !== 3) {
        alert('Please enter a valid 3-digit CVC');
        return;
      }
      
      if (!accountId || isNaN(depositAmount) || depositAmount <= 0) {
        alert('Please enter a valid deposit amount');
        return;
      }
      
      // Load accounts and find the account to update
      const accounts = loadAccounts();
      const accountIndex = accounts.findIndex(acc => acc.id === accountId);
      
      if (accountIndex === -1) {
        alert('Account not found');
        return;
      }
      
      // Update account balance
      accounts[accountIndex].balance += depositAmount;
      saveAccounts(accounts);
      
      // Re-render accounts to update the UI
      renderAccounts();
      
      // Update the balance display in the card directly (for immediate feedback)
      const balanceEl = document.querySelector(`[data-account-balance="${accountId}"]`);
      if (balanceEl) {
        balanceEl.textContent = `$${accounts[accountIndex].balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      }
      
      // Close modal and reset form
      depositModal.classList.add('hidden');
      document.body.style.overflow = '';
      amountInput.value = '';
      if (cardNumberInput) cardNumberInput.value = '';
      if (cardDateInput) cardDateInput.value = '';
      if (cardCVCInput) cardCVCInput.value = '';
      if (document.getElementById('deposit-card-name')) document.getElementById('deposit-card-name').value = '';
    });
    
    // Handle account type change
    accountTypeSelect.addEventListener('change', (e) => {
      e.stopPropagation();
      
      const accountId = parseInt(depositModal.dataset.accountId);
      if (!accountId) return;
      
      const newAccountType = accountTypeSelect.value;
      
      // Load accounts and find the account to update
      const accounts = loadAccounts();
      const accountIndex = accounts.findIndex(acc => acc.id === accountId);
      
      if (accountIndex === -1) return;
      
      // If changing to "current", make sure only one account is current
      if (newAccountType === 'current') {
        // Find any other account that is currently "current"
        accounts.forEach((acc, index) => {
          if (index !== accountIndex && acc.accountType === 'current') {
            // Change the other account to "savings"
            acc.accountType = 'savings';
          }
        });
      }
      
      // Update account type
      accounts[accountIndex].accountType = newAccountType;
      saveAccounts(accounts);
      
      // Re-render accounts to update the UI
      renderAccounts();
    });
    
    // Handle delete account
    if (deleteSubmit) {
      deleteSubmit.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        const accountId = parseInt(depositModal.dataset.accountId);
        if (!accountId) return;
        
        // Confirm deletion
        if (!confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
          return;
        }
        
        // Load accounts and remove the account
        const accounts = loadAccounts();
        const filteredAccounts = accounts.filter(acc => acc.id !== accountId);
        saveAccounts(filteredAccounts);
        
        // Re-render accounts to update the UI
        renderAccounts();
        
        // Close modal
        depositModal.classList.add('hidden');
        document.body.style.overflow = '';
      });
    }
    
    // Stop propagation on modal content to prevent closing when clicking inside
    const modalContent = depositModal.querySelector('div.rounded-xl');
    if (modalContent) {
      modalContent.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    }
    
    // Stop propagation on form inputs
    const formInputs = depositModal.querySelectorAll('input, select, button');
    formInputs.forEach(input => {
      input.addEventListener('click', (e) => {
        e.stopPropagation();
      });
      input.addEventListener('focus', (e) => {
        e.stopPropagation();
      });
    });
  }
  
  // Handle add account form submission
  if (addAccountSubmit && addAccountModal) {
    addAccountSubmit.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      
      const bankNameInput = document.getElementById('account-bank-name');
      const ibanInput = document.getElementById('account-iban');
      const accountTypeSelect = document.getElementById('account-type');
      
      if (!bankNameInput || !ibanInput || !accountTypeSelect) return;
      
      const bankName = bankNameInput.value.trim();
      const iban = ibanInput.value.trim();
      const accountType = accountTypeSelect.value;
      
      // Validate inputs
      if (!bankName || !iban) {
        alert('Please fill in all fields');
        return;
      }
      
      // Load existing accounts
      const accounts = loadAccounts();
      
      // If adding a new "current" account, change any existing "current" account to "savings"
      if (accountType === 'current') {
        accounts.forEach(acc => {
          if (acc.accountType === 'current') {
            acc.accountType = 'savings';
          }
        });
      }
      
      // Create new account
      const newAccount = {
        id: Date.now(),
        bankName: bankName,
        iban: iban,
        accountType: accountType,
        balance: 0 // Start with zero balance
      };
      
      // Add to accounts list
      accounts.push(newAccount);
      saveAccounts(accounts);
      
      // Render updated accounts
      renderAccounts();
      
      // Reset form
      bankNameInput.value = '';
      ibanInput.value = '';
      accountTypeSelect.value = 'current';
      
      // Close modal
      addAccountModal.classList.add('hidden');
      document.body.style.overflow = '';
    });
  }
  
  // Stop propagation on modal content to prevent closing when clicking inside
  if (addAccountModal) {
    const modalContent = addAccountModal.querySelector('div.rounded-xl');
    if (modalContent) {
      modalContent.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    }
    
    // Stop propagation on form inputs
    const formInputs = addAccountModal.querySelectorAll('input, select, button');
    formInputs.forEach(input => {
      input.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    });
  }
  
  // Initialize deposit modal
  initDepositModal();
  
  // Initial render
  renderAccounts();
}

// Helper functions for user-specific localStorage
function getUserStorageKey(key) {
  const userId = getCurrentUserId();
  return userId ? `user_${userId}_${key}` : key;
}

function getUserItem(key) {
  const userId = getCurrentUserId();
  if (!userId) {
    // Fallback to global storage if not logged in
    return localStorage.getItem(key);
  }
  // Try user-specific storage first
  const userKey = getUserStorageKey(key);
  const value = localStorage.getItem(userKey);
  if (value !== null) return value;
  // Fallback to global storage for backward compatibility
  return localStorage.getItem(key);
}

function setUserItem(key, value) {
  const userId = getCurrentUserId();
  if (!userId) {
    // Fallback to global storage if not logged in
    localStorage.setItem(key, value);
    return;
  }
  // Save to user-specific storage
  const userKey = getUserStorageKey(key);
  localStorage.setItem(userKey, value);
  
  // Also update user data in auth system if available
  if (typeof updateUserProperty === 'function') {
    // Map common keys to user properties
    const propertyMap = {
      'userName': 'name',
      'selectedLanguage': 'language',
      'theme': 'theme',
      'notifications': 'notifications',
      'watchlist': 'watchlist',
      'tickerPrices': 'cryptoPrices',
      'stockPrices': 'stockPrices'
    };
    
    if (propertyMap[key]) {
      try {
        const parsedValue = value.startsWith('{') || value.startsWith('[') 
          ? JSON.parse(value) 
          : value;
        updateUserProperty(userId, propertyMap[key], parsedValue);
      } catch (e) {
        updateUserProperty(userId, propertyMap[key], value);
      }
    }
  }
}

// Initialize mobile menu toggle
function initMobileMenu() {
  const menuToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  const menuIcon = document.getElementById('menu-icon');
  
  if (!menuToggle || !mobileMenu) {
    // Elements not found, skip initialization
    return;
  }
  
  // Toggle menu on button click
  menuToggle.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const isHidden = mobileMenu.classList.contains('hidden');
    
    if (isHidden) {
      // Show menu
      mobileMenu.classList.remove('hidden');
      // Change icon to X
      if (menuIcon) {
        menuIcon.innerHTML = `
          <line x1="18" x2="6" y1="6" y2="18"></line>
          <line x1="6" x2="18" y1="6" y2="18"></line>
        `;
      }
    } else {
      // Hide menu
      mobileMenu.classList.add('hidden');
      // Change icon back to menu
      if (menuIcon) {
        menuIcon.innerHTML = `
          <line x1="4" x2="20" y1="12" y2="12"></line>
          <line x1="4" x2="20" y1="6" y2="6"></line>
          <line x1="4" x2="20" y1="18" y2="18"></line>
        `;
      }
    }
  });
  
  // Close menu when clicking on a link
  const menuLinks = mobileMenu.querySelectorAll('a');
  menuLinks.forEach(link => {
    link.addEventListener('click', () => {
      mobileMenu.classList.add('hidden');
      if (menuIcon) {
        menuIcon.innerHTML = `
          <line x1="4" x2="20" y1="12" y2="12"></line>
          <line x1="4" x2="20" y1="6" y2="6"></line>
          <line x1="4" x2="20" y1="18" y2="18"></line>
        `;
      }
    });
  });
  
  // Close menu when clicking outside (only if menu is open)
  document.addEventListener('click', (e) => {
    if (!mobileMenu.classList.contains('hidden')) {
      if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        mobileMenu.classList.add('hidden');
        if (menuIcon) {
          menuIcon.innerHTML = `
          <line x1="4" x2="20" y1="12" y2="12"></line>
          <line x1="4" x2="20" y1="6" y2="6"></line>
          <line x1="4" x2="20" y1="18" y2="18"></line>
        `;
        }
      }
    }
  });
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
  // Check if we're on login page first
  const currentPath = window.location.pathname;
  const currentHref = window.location.href;
  const isLoginPage = currentPath.includes('login.html') || 
                     currentHref.includes('login.html');
  
  // If on login page, skip initialization
  if (isLoginPage) {
    return; // Let login page handle its own initialization
  }
  
  // ALWAYS initialize the page immediately - NO AUTH CHECK
  // Auth is handled by login page only to prevent navigation loops
  initializePage();
});

// Separate initialization function
function initializePage() {
  
  // Load user data from auth system if available
  const userId = getCurrentUserId();
  if (userId && typeof getCurrentUser === 'function') {
    const user = getCurrentUser();
    if (user) {
      // Load user name
      if (user.name && !localStorage.getItem(getUserStorageKey('userName'))) {
        setUserItem('userName', user.name);
      }
      // Load preferences
      if (user.theme) setUserItem('theme', user.theme);
      if (user.language) setUserItem('selectedLanguage', user.language);
      if (user.notifications !== undefined) setUserItem('notifications', user.notifications.toString());
      if (user.watchlist) setUserItem('watchlist', JSON.stringify(user.watchlist));
      if (user.cryptoPrices) setUserItem('tickerPrices', JSON.stringify(user.cryptoPrices));
      if (user.stockPrices) setUserItem('stockPrices', JSON.stringify(user.stockPrices));
    }
  }
  
  initTheme(); // Initialize theme first
  loadUserName(); // Load saved name on all pages
  updateHeaderBalance(); // Update header balance on all pages
  initMobileMenu(); // Initialize mobile menu toggle
  initTabs();
  initModals();
  initLanguageDropdown();
  initForecastPage();
  initProfilePage();
  initBankPage(); // Initialize bank page with account management
  initMarketTicker(); // Initialize market ticker with real data
  initTradingPage(); // Initialize trading page with real crypto data
  initSharesPage(); // Initialize shares page with real stock data
  initTradeModalClose(); // Initialize trade modal close handlers
  initWatchlist(); // Initialize watchlist functionality
  initStockModal(); // Initialize stock detail modal
}

