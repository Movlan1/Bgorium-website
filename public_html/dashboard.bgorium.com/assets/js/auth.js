// Authentication and User Management System
// Stores user data in JSON format in localStorage

// Simple hash function for password (in production, use proper hashing)
function simpleHash(str) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    const char = str.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash = hash & hash; // Convert to 32bit integer
  }
  return hash.toString();
}

// Get all users from storage
function getAllUsers() {
  const usersJson = localStorage.getItem('blackGoldUsers');
  if (usersJson) {
    try {
      return JSON.parse(usersJson);
    } catch (e) {
      console.error('Error parsing users:', e);
      return {};
    }
  }
  return {};
}

// Save all users to storage
function saveAllUsers(users) {
  localStorage.setItem('blackGoldUsers', JSON.stringify(users));
}

// Get current logged in user
function getCurrentUser() {
  const userId = localStorage.getItem('currentUserId');
  if (!userId) return null;
  
  const users = getAllUsers();
  return users[userId] || null;
}

// Set current logged in user
function setCurrentUser(userId) {
  localStorage.setItem('currentUserId', userId);
}

// Logout current user
function logout() {
  localStorage.removeItem('currentUserId');
  window.location.href = 'login.html';
}

// Get user data structure
function createUserData(username, email, name, passwordHash) {
  return {
    id: Date.now().toString(),
    username: username,
    email: email,
    name: name,
    passwordHash: passwordHash,
    createdAt: new Date().toISOString(),
    // Account properties
    accounts: [], // Bank accounts array
    currentAccountId: null, // ID of current account
    // Preferences
    theme: 'dark',
    language: 'en',
    notifications: true,
    // Trading data
    watchlist: [], // Stock watchlist
    cryptoPrices: {}, // Cached crypto prices
    stockPrices: {}, // Cached stock prices
    // Other data
    stakedTokens: 0,
    country: '',
    phone: ''
  };
}

// Register new user
function registerUser(username, email, name, password) {
  const users = getAllUsers();
  
  // Check if username already exists
  for (const userId in users) {
    if (users[userId].username === username || users[userId].email === email) {
      return { success: false, message: 'Username or email already exists' };
    }
  }
  
  // Validate password
  if (password.length < 6) {
    return { success: false, message: 'Password must be at least 6 characters' };
  }
  
  // Create new user
  const passwordHash = simpleHash(password);
  const userData = createUserData(username, email, name, passwordHash);
  
  users[userData.id] = userData;
  saveAllUsers(users);
  
  return { success: true, userId: userData.id };
}

// Login user
function loginUser(usernameOrEmail, password) {
  const users = getAllUsers();
  const passwordHash = simpleHash(password);
  
  // Find user by username or email
  for (const userId in users) {
    const user = users[userId];
    if ((user.username === usernameOrEmail || user.email === usernameOrEmail) && 
        user.passwordHash === passwordHash) {
      setCurrentUser(userId);
      return { success: true, userId: userId, user: user };
    }
  }
  
  return { success: false, message: 'Invalid username/email or password' };
}

// Get user's bank accounts
function getUserAccounts(userId) {
  const users = getAllUsers();
  const user = users[userId];
  if (!user) return [];
  return user.accounts || [];
}

// Save user's bank accounts
function saveUserAccounts(userId, accounts) {
  const users = getAllUsers();
  if (!users[userId]) return false;
  
  users[userId].accounts = accounts;
  saveAllUsers(users);
  return true;
}

// Get user's current account
function getUserCurrentAccount(userId) {
  const users = getAllUsers();
  const user = users[userId];
  if (!user) return null;
  
  if (!user.currentAccountId) return null;
  
  const accounts = user.accounts || [];
  return accounts.find(acc => acc.id === user.currentAccountId) || null;
}

// Set user's current account
function setUserCurrentAccount(userId, accountId) {
  const users = getAllUsers();
  if (!users[userId]) return false;
  
  users[userId].currentAccountId = accountId;
  saveAllUsers(users);
  return true;
}

// Update user property
function updateUserProperty(userId, property, value) {
  const users = getAllUsers();
  if (!users[userId]) return false;
  
  users[userId][property] = value;
  saveAllUsers(users);
  return true;
}

// Get user property
function getUserProperty(userId, property) {
  const users = getAllUsers();
  const user = users[userId];
  if (!user) return null;
  return user[property];
}

// Initialize login/register page
function initAuthPage() {
  const loginTab = document.getElementById('login-tab');
  const registerTab = document.getElementById('register-tab');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  const loginSubmit = document.getElementById('login-submit');
  const registerSubmit = document.getElementById('register-submit');
  const authMessage = document.getElementById('auth-message');
  
  // Check if user is already logged in
  const currentUser = getCurrentUser();
  if (currentUser) {
    // Use replace to prevent redirect loops
    window.location.replace('index.html');
    return;
  }
  
  // Show message
  function showMessage(text, isError = false) {
    authMessage.textContent = text;
    authMessage.className = `mb-4 p-3 rounded-lg text-sm ${
      isError 
        ? 'bg-red-600/20 border border-red-600/30 text-red-400' 
        : 'bg-green-600/20 border border-green-600/30 text-green-400'
    }`;
    authMessage.classList.remove('hidden');
    
    setTimeout(() => {
      authMessage.classList.add('hidden');
    }, 5000);
  }
  
  // Tab switching
  function switchToLogin() {
    loginTab.classList.add('bg-gold-600/20', 'text-gold-400');
    loginTab.classList.remove('text-gold-600', 'hover:text-gold-400', 'hover:bg-gold-600/10');
    registerTab.classList.remove('bg-gold-600/20', 'text-gold-400');
    registerTab.classList.add('text-gold-600', 'hover:text-gold-400', 'hover:bg-gold-600/10');
    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');
    authMessage.classList.add('hidden');
  }
  
  function switchToRegister() {
    registerTab.classList.add('bg-gold-600/20', 'text-gold-400');
    registerTab.classList.remove('text-gold-600', 'hover:text-gold-400', 'hover:bg-gold-600/10');
    loginTab.classList.remove('bg-gold-600/20', 'text-gold-400');
    loginTab.classList.add('text-gold-600', 'hover:text-gold-400', 'hover:bg-gold-600/10');
    registerForm.classList.remove('hidden');
    loginForm.classList.add('hidden');
    authMessage.classList.add('hidden');
  }
  
  loginTab.addEventListener('click', switchToLogin);
  registerTab.addEventListener('click', switchToRegister);
  
  // Login form submission
  if (loginSubmit) {
    loginSubmit.addEventListener('click', (e) => {
      e.preventDefault();
      
      const username = document.getElementById('login-username').value.trim();
      const password = document.getElementById('login-password').value;
      
      if (!username || !password) {
        showMessage('Please fill in all fields', true);
        return;
      }
      
      const result = loginUser(username, password);
      if (result.success) {
        showMessage('Login successful! Redirecting...', false);
        // Use replace instead of href to prevent back button issues
        setTimeout(() => {
          window.location.replace('index.html');
        }, 500);
      } else {
        showMessage(result.message || 'Login failed', true);
      }
    });
    
    // Allow Enter key to submit
    document.getElementById('login-password').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        loginSubmit.click();
      }
    });
  }
  
  // Register form submission
  if (registerSubmit) {
    registerSubmit.addEventListener('click', (e) => {
      e.preventDefault();
      
      const name = document.getElementById('register-name').value.trim();
      const username = document.getElementById('register-username').value.trim();
      const email = document.getElementById('register-email').value.trim();
      const password = document.getElementById('register-password').value;
      const confirmPassword = document.getElementById('register-confirm-password').value;
      
      if (!name || !username || !email || !password || !confirmPassword) {
        showMessage('Please fill in all fields', true);
        return;
      }
      
      if (password !== confirmPassword) {
        showMessage('Passwords do not match', true);
        return;
      }
      
      if (password.length < 6) {
        showMessage('Password must be at least 6 characters', true);
        return;
      }
      
      // Basic email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        showMessage('Please enter a valid email address', true);
        return;
      }
      
      const result = registerUser(username, email, name, password);
      if (result.success) {
        // Auto-login after registration
        setCurrentUser(result.userId);
        showMessage('Account created successfully! Redirecting...', false);
        // Use replace instead of href to prevent back button issues
        setTimeout(() => {
          window.location.replace('index.html');
        }, 500);
      } else {
        showMessage(result.message || 'Registration failed', true);
      }
    });
    
    // Allow Enter key to submit
    document.getElementById('register-confirm-password').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        registerSubmit.click();
      }
    });
  }
}

// Initialize when DOM is ready - ONLY on login page
function shouldInitAuth() {
  const currentPath = window.location.pathname;
  const currentHref = window.location.href;
  return currentPath.includes('login.html') || currentHref.includes('login.html');
}

if (shouldInitAuth()) {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthPage);
  } else {
    initAuthPage();
  }
}

