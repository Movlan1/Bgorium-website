// Set Tailwind CSS gradient position variables and fix gradients dynamically
(function() {
  'use strict';
  
  // Color mapping for all gradient colors
  const colorMap = {
    'gold': { 400: '#ffd700', 500: '#f5c842', 600: '#e6a532' },
    'orange': { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' },
    'green': { 400: '#4ade80', 500: '#22c55e', 600: '#16a34a' },
    'black': { default: '#000000' },
    'gray': { 900: '#111827' }
  };
  
  function setGradientVariables() {
    const root = document.documentElement;
    root.style.setProperty('--tw-gradient-from-position', '0%', 'important');
    root.style.setProperty('--tw-gradient-via-position', '50%', 'important');
    root.style.setProperty('--tw-gradient-to-position', '100%', 'important');
  }
  
  // Helper function to safely get class string from any element
  function getClassString(el) {
    if (!el) return '';
    try {
      if (el.getAttribute) {
        const classAttr = el.getAttribute('class');
        if (typeof classAttr === 'string') {
          return classAttr;
        }
      }
      if (typeof el.className === 'string') {
        return el.className;
      }
      if (el.className && typeof el.className === 'object') {
        // Handle DOMTokenList or SVGAnimatedString
        const result = el.className.baseVal || el.className.toString() || '';
        return typeof result === 'string' ? result : String(result);
      }
    } catch (e) {
      // Ignore errors
    }
    return '';
  }
  
  // Safe includes function that never throws
  function safeIncludes(str, searchString) {
    try {
      if (!str || typeof str !== 'string') {
        return false;
      }
      return str.includes(searchString);
    } catch (e) {
      return false;
    }
  }
  
  function getGradientDirection(classes) {
    // Ensure classes is a string
    const classStr = typeof classes === 'string' ? classes : (classes?.toString() || '');
    if (classStr.includes('bg-gradient-to-br')) return 'to bottom right';
    if (classStr.includes('bg-gradient-to-b')) return 'to bottom';
    if (classStr.includes('bg-gradient-to-l')) return 'to left';
    if (classStr.includes('bg-gradient-to-t')) return 'to top';
    if (classStr.includes('bg-gradient-to-tl')) return 'to top left';
    if (classStr.includes('bg-gradient-to-tr')) return 'to top right';
    if (classStr.includes('bg-gradient-to-bl')) return 'to bottom left';
    return 'to right'; // default
  }
  
  function applyGradientToElement(el, fromColor, toColor, direction) {
    if (fromColor && toColor) {
      // Force apply gradient with !important to override CSS variables
      el.style.setProperty('background-image', `linear-gradient(${direction}, ${fromColor}, ${toColor})`, 'important');
      // Also fix the CSS variables to prevent conflicts
      el.style.setProperty('--tw-gradient-from', fromColor, 'important');
      el.style.setProperty('--tw-gradient-to', toColor, 'important');
    }
  }
  
  function fixGradientElements() {
    // Find all elements with gradient classes - more comprehensive selector
    const selectors = [
      '[class*="bg-gradient-"]',
      '[class*="from-gold"]',
      '[class*="to-gold"]',
      '[class*="from-orange"]',
      '[class*="to-orange"]',
      '[class*="from-green"]',
      '[class*="to-green"]'
    ];
    
    const allElements = new Set();
    selectors.forEach(selector => {
      document.querySelectorAll(selector).forEach(el => allElements.add(el));
    });
    
    allElements.forEach(el => {
      // Get class string safely using helper function
      const classes = getClassString(el);
      
      // Skip if already processed (has data-gradient-fixed attribute)
      if (el.dataset.gradientFixed === 'true') return;
      
      // Check if element has both from- and to- classes
      const fromMatch = classes.match(/\bfrom-(\w+)-(\d+)\b/);
      const toMatch = classes.match(/\bto-(\w+)-(\d+)\b/);
      
      if (fromMatch && toMatch) {
        const fromColor = colorMap[fromMatch[1]]?.[fromMatch[2]] || colorMap[fromMatch[1]]?.default;
        const toColor = colorMap[toMatch[1]]?.[toMatch[2]] || colorMap[toMatch[1]]?.default;
        const direction = getGradientDirection(classes);
        
        if (fromColor && toColor) {
          applyGradientToElement(el, fromColor, toColor, direction);
          el.dataset.gradientFixed = 'true';
          el.dataset.gradientFrom = fromColor;
          el.dataset.gradientTo = toColor;
          el.dataset.gradientDirection = direction;
        }
      }
    });
    
    // Handle hover states - add event listeners for buttons with hover gradients
    allElements.forEach(el => {
      // Get class string safely using helper function
      const classes = getClassString(el);
      
      const hoverFromMatch = classes.match(/\bhover:from-(\w+)-(\d+)\b/);
      const hoverToMatch = classes.match(/\bhover:to-(\w+)-(\d+)\b/);
      
      if (hoverFromMatch && hoverToMatch) {
        const hoverFromColor = colorMap[hoverFromMatch[1]]?.[hoverFromMatch[2]] || colorMap[hoverFromMatch[1]]?.default;
        const hoverToColor = colorMap[hoverToMatch[1]]?.[hoverToMatch[2]] || colorMap[hoverToMatch[1]]?.default;
        const direction = getGradientDirection(classes);
        
        if (hoverFromColor && hoverToColor) {
          // Store original gradient
          const originalFrom = el.dataset.gradientFrom;
          const originalTo = el.dataset.gradientTo;
          const originalDirection = el.dataset.gradientDirection || direction;
          
          // Remove existing listeners to avoid duplicates
          const existingHover = el.dataset.hoverListener;
          if (!existingHover) {
            el.dataset.hoverListener = 'true';
            
            el.addEventListener('mouseenter', () => {
              applyGradientToElement(el, hoverFromColor, hoverToColor, direction);
            });
            
            el.addEventListener('mouseleave', () => {
              if (originalFrom && originalTo) {
                applyGradientToElement(el, originalFrom, originalTo, originalDirection);
              }
            });
          }
        }
      }
    });
  }
  
  // MutationObserver to catch dynamically added elements
  function setupMutationObserver() {
    const observer = new MutationObserver((mutations) => {
      // Wrap everything in try-catch to suppress any errors
      try {
        // Early exit: Check if ANY mutation is in ticker container - skip all processing
        let hasTickerMutation = false;
        try {
          for (let i = 0; i < mutations.length; i++) {
            const mut = mutations[i];
            if (mut && mut.target) {
              try {
                if (mut.target.closest && mut.target.closest('[data-ticker-container]')) {
                  hasTickerMutation = true;
                  break;
                }
                // Check added nodes
                if (mut.addedNodes && mut.addedNodes.length > 0) {
                  for (let j = 0; j < mut.addedNodes.length; j++) {
                    const node = mut.addedNodes[j];
                    if (node && node.closest && node.closest('[data-ticker-container]')) {
                      hasTickerMutation = true;
                      break;
                    }
                  }
                }
              } catch (e) {
                // Ignore
              }
            }
          }
        } catch (e) {
          // Ignore
        }
        
        // If any mutation is ticker-related, completely skip processing
        if (hasTickerMutation) {
          return;
        }
        
        let shouldFix = false;
        mutations.forEach((mutation) => {
          try {
            // Safety: Skip if mutation target doesn't have expected properties
            if (!mutation || !mutation.target) {
              return;
            }
            // Skip if mutation is in ticker container (text updates)
            const target = mutation.target;
            try {
              if (target && (target.closest && target.closest('[data-ticker-container]'))) {
                return; // Ignore ticker updates
              }
              // Also skip if target is a text node
              if (target && target.nodeType === 3) {
                return; // Text node
              }
            } catch (e) {
              // If closest fails, skip this mutation
              return;
            }
            
            // Ignore text-only changes (like ticker updates)
            if (mutation.type === 'childList') {
              // Only process if actual elements are added/removed, not just text nodes
              const hasElementNodes = Array.from(mutation.addedNodes).some(node => 
                node && node.nodeType === 1 && node.tagName
              ) || Array.from(mutation.removedNodes).some(node => 
                node && node.nodeType === 1 && node.tagName
              );
              
              if (!hasElementNodes) {
                return; // Skip text-only changes
              }
              
              if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach((node) => {
                  try {
                    // Only process element nodes, skip text nodes
                    if (node && node.nodeType === 1 && node.tagName) { // Element node with tagName
                      // Skip if in ticker container
                      if (node.closest && node.closest('[data-ticker-container]')) {
                        return;
                      }
                      
                      // Get class string safely using helper function
                      let classes = getClassString(node);
                      // Double-check it's a string before using .includes()
                      classes = typeof classes === 'string' ? classes : String(classes || '');
                      
                      // Use safe includes to prevent errors
                      if (classes && (
                          safeIncludes(classes, 'bg-gradient-') || 
                          safeIncludes(classes, 'from-gold') || 
                          safeIncludes(classes, 'to-gold') ||
                          safeIncludes(classes, 'from-orange') ||
                          safeIncludes(classes, 'to-orange') ||
                          safeIncludes(classes, 'from-green') ||
                          safeIncludes(classes, 'to-green'))) {
                        shouldFix = true;
                      }
                      // Also check children
                      if (node.querySelectorAll) {
                        const gradientChildren = node.querySelectorAll('[class*="bg-gradient-"], [class*="from-gold"], [class*="to-gold"]');
                        if (gradientChildren.length > 0) {
                          shouldFix = true;
                        }
                      }
                    }
                  } catch (e) {
                    // Silently ignore errors for individual nodes
                  }
                });
              }
            } else if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
              try {
                // Skip if mutation is in ticker container
                if (mutation.target && mutation.target.closest && mutation.target.closest('[data-ticker-container]')) {
                  return;
                }
                
                // Get class string safely using helper function
                let classes = getClassString(mutation.target);
                // Double-check it's a string before using .includes()
                classes = typeof classes === 'string' ? classes : String(classes || '');
                
                // Use safe includes to prevent errors
                if (classes && (
                    safeIncludes(classes, 'bg-gradient-') || 
                    safeIncludes(classes, 'from-gold') || 
                    safeIncludes(classes, 'to-gold'))) {
                  shouldFix = true;
                }
              } catch (e) {
                // Silently ignore errors for attribute mutations
              }
            }
          } catch (e) {
            // Silently ignore errors for individual mutations
          }
        });
        
        if (shouldFix) {
          setTimeout(fixGradientElements, 50);
        }
      } catch (e) {
        // Silently suppress all errors to prevent console spam
      }
    });
    
    observer.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['class'],
      characterData: false // Explicitly ignore text content changes
    });
  }
  
  // Set variables immediately
  setGradientVariables();
  
  // Fix gradients after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      setGradientVariables();
      setTimeout(fixGradientElements, 100);
      setupMutationObserver();
    });
  } else {
    setGradientVariables();
    setTimeout(fixGradientElements, 100);
    setupMutationObserver();
  }
  
  // Fix gradients after page load
  window.addEventListener('load', () => {
    setGradientVariables();
    fixGradientElements();
  });
  
  // Fix gradients with delays to catch late-loading content
  setTimeout(() => { setGradientVariables(); fixGradientElements(); }, 200);
  setTimeout(() => { setGradientVariables(); fixGradientElements(); }, 500);
  setTimeout(() => { setGradientVariables(); fixGradientElements(); }, 1000);
  setTimeout(() => { setGradientVariables(); fixGradientElements(); }, 2000);
})();
