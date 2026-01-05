import os
import re

# Payment modal HTML template
PAYMENT_MODAL = '''
    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Complete Your Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <!-- Customer Details -->
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="customerName" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerEmail" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="customerEmail" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerPhone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="customerPhone" placeholder="Enter your phone number" required>
                        </div>

                        <hr>

                        <!-- Payment Method Selection -->
                        <div class="mb-3">
                            <label class="form-label">Select Payment Method *</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="creditCard" value="Credit Card" checked>
                                <label class="form-check-label" for="creditCard">
                                    <i class="fas fa-credit-card me-2"></i> Credit Card
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="debitCard" value="Debit Card">
                                <label class="form-check-label" for="debitCard">
                                    <i class="fas fa-credit-card me-2"></i> Debit Card
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="upi" value="UPI">
                                <label class="form-check-label" for="upi">
                                    <i class="fas fa-mobile-alt me-2"></i> UPI
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="netBanking" value="Net Banking">
                                <label class="form-check-label" for="netBanking">
                                    <i class="fas fa-university me-2"></i> Net Banking
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="qrCode" value="QRCode">
                                <label class="form-check-label" for="qrCode">
                                    <i class="fas fa-qrcode me-2"></i> QR Code
                                </label>
                            </div>
                        </div>

                        <!-- QR Code Container -->
                        <div id="qrCodeContainer" class="text-center d-none mb-3">
                            <p class="fw-bold">Scan this QR Code to Pay:</p>
                            <img src="image2/payment .jpg" alt="Payment QR Code" class="img-fluid border p-2" style="max-width: 300px;">
                            <p class="text-muted small mt-2">After payment, click "Pay Now" to confirm your booking</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="payNowBtn">Pay Now</button>
                </div>
            </div>
        </div>
    </div>
'''

# Directory containing state HTML files
BASE_DIR = r"d:/MCA/WEB DEV/my project/TourTravel/TourAndTravel"

# List of state files to update (excluding maharashtra which is already done)
STATE_FILES = [
    "state_Delhi.html",
    "state_Gujarat.html",
    "state_kerala.html",
    "state_Jammu&Kashmir.html",
    "state_rajasthan.html",
    "state_uttarpradesh.html",
    "state_westbengal.html"
]

def update_state_file(filepath):
    """Update a state HTML file with payment modal and necessary changes"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Skip if modal already exists
        if 'id="paymentModal"' in content:
            print(f"✓ {os.path.basename(filepath)} - Modal already exists, skipping")
            return
        
        # 1. Update footer to use bg-custom-nav
        content = re.sub(
            r'<footer class="bg-dark text-white',
            r'<footer class="bg-custom-nav text-dark',
            content
        )
        
        # 2. Add book-btn class to all booking buttons
        # Match various button patterns
        content = re.sub(
            r'(<button[^>]*class="[^"]*btn[^"]*)(btn-info text-white|btn-primary|btn-warning)([^"]*"[^>]*>Book[^<]*</button>)',
            r'\1btn-custom-nav book-btn\3',
            content
        )
        
        # 3. Add script.js if not present
        if 'script.js' not in content:
            content = re.sub(
                r'(<script src="https://cdn\.jsdelivr\.net/npm/bootstrap@[^"]+"></script>)',
                r'\1\n    <script src="script.js"></script>',
                content
            )
        
        # 4. Add payment modal before closing </body> tag
        content = re.sub(
            r'(\s*</body>)',
            PAYMENT_MODAL + r'\n\n    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>\n    <script src="script.js"></script>\n</body>',
            content
        )
        
        # Remove duplicate script tags if any
        content = re.sub(
            r'(<script src="https://cdn\.jsdelivr\.net/npm/bootstrap@[^"]+"></script>\s*)+',
            r'<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>\n    ',
            content
        )
        content = re.sub(
            r'(<script src="script\.js"></script>\s*)+',
            r'<script src="script.js"></script>\n',
            content
        )
        
        # Write updated content
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print(f"✓ {os.path.basename(filepath)} - Successfully updated")
        
    except Exception as e:
        print(f"✗ {os.path.basename(filepath)} - Error: {str(e)}")

def main():
    print("Starting payment modal integration...\n")
    
    for filename in STATE_FILES:
        filepath = os.path.join(BASE_DIR, filename)
        if os.path.exists(filepath):
            update_state_file(filepath)
        else:
            print(f"✗ {filename} - File not found")
    
    print("\nPayment modal integration complete!")

if __name__ == "__main__":
    main()