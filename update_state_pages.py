"""
Script to update all state HTML pages with cart and rating functionality
This script adds:
1. Cart icon to navbar
2. Add to Cart buttons instead of Book buttons
3. Rating display and interactive rating components
"""

import re
import os

# List of state files to update
state_files = [
    'state_maharashtra.html',
    'state_Jammu&Kashmir.html',
    'state_kerala.html',
    'state_rajasthan.html',
    'state_Gujarat.html',
    'state_uttarpradesh.html',
    'state_westbengal.html'
]

base_path = r'd:\MCA\WEB DEV\my project\TourTravel\TourAndTravel'

def add_cart_icon_to_navbar(content):
    """Add cart icon to navbar"""
    # Find the navbar closing </ul> tag and add cart icon before it
    pattern = r'(\s+</ul>\s+</div>\s+</nav>)'
    replacement = r'''                <li class="nav-item">
                    <div class="cart-icon" title="View Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" style="display: none;">0</span>
                    </div>
                </li>
\1'''
    
    # Only add if not already present
    if 'cart-icon' not in content:
        content = re.sub(pattern, replacement, content, count=1)
    
    return content

def update_package_cards(content, state_name):
    """Update package cards with cart buttons and ratings"""
    
    # Pattern to find package cards
    # This will match cards with pricing information
    card_pattern = r'(<div class="card[^>]*?)\s*(data-package="[^"]*")?\s*(data-state="[^"]*")?\s*>'
    
    def replace_card(match):
        card_opening = match.group(0)
        # Add data attributes if not present
        if 'data-package=' not in card_opening and 'data-state=' not in card_opening:
            # We'll add these dynamically based on package name
            return card_opening
        return card_opening
    
    # First, let's find and replace "Book" buttons with "Add to Cart" buttons
    # Pattern for book buttons
    book_button_pattern = r'<button[^>]*class="[^"]*book-btn[^"]*"[^>]*>.*?</button>'
    add_to_cart_button = '''<button type="button" class="btn btn-lg btn-block btn-add-to-cart add-to-cart-btn mt-3">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>'''
    
    content = re.sub(book_button_pattern, add_to_cart_button, content, flags=re.DOTALL)
    
    return content

def add_rating_components_to_cards(content):
    """Add rating display and interactive rating to package cards"""
    
    # Find all pricing cards and add rating components before the button
    # Pattern to find the area just before buttons
    pattern = r'(</ul>\s*)((?:<button|<div class="rate-package))'
    
    rating_html = r'''\1
                        
                        <!-- Rating Display -->
                        <div class="rating-container">
                            <div class="stars rating-display"></div>
                            <div class="rating-info"></div>
                        </div>
                        
                        <!-- Rate this package -->
                        <div class="rate-package">
                            <div class="rate-package-title">Rate this package:</div>
                            <div class="stars rating-interactive"></div>
                        </div>
                        
                        \2'''
    
    # Only add if not already present
    if 'rating-container' not in content:
        content = re.sub(pattern, rating_html, content, flags=re.DOTALL)
    
    return content

# Process each file
for filename in state_files:
    filepath = os.path.join(base_path, filename)
    
    if not os.path.exists(filepath):
        print(f"File not found: {filepath}")
        continue
    
    print(f"Processing {filename}...")
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Extract state name from filename
        state_name = filename.replace('state_', '').replace('.html', '').replace('&', ' & ')
        
        # Apply transformations
        content = add_cart_icon_to_navbar(content)
        content = update_package_cards(content, state_name)
        content = add_rating_components_to_cards(content)
        
        # Write back
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print(f"✓ Updated {filename}")
        
    except Exception as e:
        print(f"✗ Error processing {filename}: {str(e)}")

print("\nAll files processed!")