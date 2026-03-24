<?php
$pageTitle = 'Operational Checkout';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit;
}

$db = DB::getInstance();
$cartItems = [];
$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

foreach ($products as $p) {
    if (!isset($_SESSION['cart'][$p['id']])) continue;
    
    $qty = $_SESSION['cart'][$p['id']];
    $subtotal += $p['price'] * $qty;
    $cartItems[] = ['product' => $p, 'quantity' => $qty];
}

// Fixed shipping calculation for simplicity in this reorganization
$shippingCost = 15.00;
$total = $subtotal + $shippingCost;
?>

<div class="pt-32 pb-24 bg-[#050505] min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <div class="page-header mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic"><?php echo __('transactional_transparency'); ?></h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('checkout'); ?></h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
            <!-- Form Steps -->
            <div class="lg:col-span-2 space-y-12">
                <div id="step-1" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] transition-all duration-700 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-white mb-10">I. <?php echo __('shipping_details'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('electronic_mail'); ?></label><input type="email" id="email" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('identity'); ?></label><input type="text" id="name" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div class="md:col-span-2"><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Secure Address</label><input type="text" id="address" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">City</label><input type="text" id="city" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Postal/Zip</label><input type="text" id="postalCode" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                    </div>
                    <button onclick="calculateDelivery()" class="btn btn-gold mt-12 w-full py-6 uppercase text-[11px] tracking-[0.4em] shadow-xl">Calculate Shipping</button>
                </div>

                <div id="step-2" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] opacity-20 pointer-events-none transition-all duration-700 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-white mb-10">II. Shipping Method</h3>
                    <div id="delivery-info" class="text-[10px] border border-white/5 p-8 rounded-[2.5rem] bg-white/[0.01] mb-8 uppercase tracking-[0.2em] text-white/40 leading-relaxed italic font-bold">Please fill out shipping details above...</div>
                    <button onclick="goToPaymentStep()" class="btn btn-gold w-full py-6 uppercase text-[11px] tracking-[0.4em] shadow-xl">Continue to Payment</button>
                </div>

                <div id="step-3" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] opacity-20 pointer-events-none transition-all duration-700 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-white mb-10">III. Payment Option</h3>
                    
                    <div class="flex gap-4 mb-8">
                        <button onclick="togglePaymentMethod('stripe')" id="btn-stripe" class="flex-1 py-4 border border-amber-600/30 bg-amber-600/10 text-amber-500 text-[10px] uppercase tracking-widest font-black rounded-2xl transition-all">Credit Card (Stripe)</button>
                        <button onclick="togglePaymentMethod('paypal')" id="btn-paypal" class="flex-1 py-4 border border-white/10 bg-white/5 text-white/50 text-[10px] uppercase tracking-widest font-black rounded-2xl transition-all hover:bg-white/10 hover:text-white">PayPal</button>
                    </div>

                    <div id="payment-stripe-container">
                        <div id="payment-element" class="mb-8 p-6 bg-white/[0.02] border border-white/5 rounded-2xl min-h-[150px] flex items-center justify-center text-white/20 text-[10px] uppercase tracking-widest font-black">[Stripe Element Mount Point]</div>
                        <button id="submit-payment" class="btn btn-gold w-full py-6 uppercase text-[11px] tracking-[0.4em] shadow-2xl">Pay with Card</button>
                    </div>

                    <div id="payment-paypal-container" class="hidden">
                        <div class="mb-8 p-8 bg-[#003087]/20 border border-[#0079C1]/30 rounded-2xl flex flex-col items-center justify-center">
                            <p class="text-[10px] text-white/70 uppercase tracking-widest text-center italic mb-4">Requires live PayPal Client ID to render smart buttons.</p>
                            <svg class="h-8 opacity-80" viewBox="0 0 200 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M72.2 2H62.7c-1 0-1.8.7-2 1.6L54 44.2c-.1.8.5 1.6 1.3 1.6h8.7c1 0 1.8-.7 2-1.6l4.6-29h4.3c8.1 0 11.5-3.3 12.6-8.7.6-2.5.5-4.8-.4-6.4C85.5 2.1 81.3 2 76.5 2h-4.3zm2.5 10H70l-1.4 8.7h4.8c4.3 0 6.1-1.6 6.7-4.2.3-1.6.2-3-.5-3.9-.9-.6-2.5-.6-4.9-.6z" fill="#0079C1"/>
                            </svg>
                        </div>
                        <button onclick="confirmOrder('pi_paypal_mock_' + Math.floor(Math.random() * 99999))" class="w-full py-6 bg-[#0079C1] hover:bg-[#005a93] text-white uppercase text-[11px] font-black tracking-[0.4em] rounded-full transition-colors shadow-2xl">Complete with PayPal</button>
                    </div>

                    <div id="payment-message" class="hidden mt-6 p-4 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest rounded-2xl text-center"></div>
                </div>
            </div>

            <!-- Summary -->
            <div class="space-y-12">
                <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] sticky top-32 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-amber-600 mb-10 italic">Summary Ledger</h3>
                    <div class="space-y-6 mb-12">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="flex justify-between items-center text-[12px] font-black uppercase tracking-widest text-white/40">
                            <span class="max-w-[70%]"><?php echo e($item['product']['name']); ?> (<?php echo $item['quantity']; ?>)</span>
                            <span class="text-white italic">$<?php echo number_format($item['product']['price'] * $item['quantity'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t border-white/5 pt-8 space-y-4">
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-white/20"><span>Base Value</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-white/20"><span>Logistics</span><span id="shipping-display">$<?php echo number_format($shippingCost, 2); ?></span></div>
                        <div class="flex justify-between text-2xl font-serif font-bold text-amber-500 pt-4 italic"><span>Consolidated</span><span id="total-display">$<?php echo number_format($total, 2); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
let stripe, elements, paymentElement;
let shippingCost = 15.00;
let subtotal = <?php echo $subtotal; ?>;

function goToStep(num) {
    [1, 2, 3].forEach(s => {
        const el = document.getElementById('step-' + s);
        if (s === num) { el.classList.remove('opacity-20', 'pointer-events-none'); }
        else { el.classList.add('opacity-40', 'pointer-events-none'); }
    });
}

async function calculateDelivery() {
    // Simulated delivery calc logic (replacing separate API file)
    const postal = document.getElementById('postalCode').value;
    const isLocal = postal.toUpperCase().startsWith('M'); // Simple GTA check
    shippingCost = isLocal ? 0 : 15.00;
    
    document.getElementById('shipping-display').innerText = '$' + shippingCost.toFixed(2);
    document.getElementById('total-display').innerText = '$' + (subtotal + shippingCost).toFixed(2);
    document.getElementById('delivery-info').innerHTML = isLocal ? 
        'LOCAL COURIER DISPATCH<br>COMPLIMENTARY ACCESS (GTA)' : 
        'POSTAL LOGISTICS<br>$15.00 CAD (STANDARD CARRIER)';
    
    goToStep(2);
}

async function goToPaymentStep() {
    const email = document.getElementById('email').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const res = await fetch('create-intent.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: `email=${encodeURIComponent(email)}&total=${subtotal + shippingCost}&csrf_token=${csrfToken}`
    });

    const { clientSecret, error } = await res.json();
    if (error) { console.warn("Payment Intent Error (Test Mode OK):", error); }

    const pk = '<?php echo defined("STRIPE_PUBLIC_KEY") ? STRIPE_PUBLIC_KEY : ""; ?>';
    if (pk && typeof Stripe !== 'undefined') {
        try {
            stripe = Stripe(pk);
            if (clientSecret) {
                elements = stripe.elements({ clientSecret, appearance: { theme: 'night' } });
                paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');
            }
        } catch(e) { console.warn("Stripe init bypassed:", e); }
    }
    
    goToStep(3);

    document.getElementById('submit-payment').onclick = async () => {
        // MOCK PAYMENTS FOR DEV PHASE:
        // Normally you'd await stripe.confirmPayment(...)
        // Since we lack API keys locally, simulate immediate success.
        
        const mockPaymentIntentId = 'pi_simulated_' + Math.floor(Math.random() * 99999);
        await confirmOrder(mockPaymentIntentId);
    };
}

function togglePaymentMethod(method) {
    const btnStripe = document.getElementById('btn-stripe');
    const btnPaypal = document.getElementById('btn-paypal');
    const contStripe = document.getElementById('payment-stripe-container');
    const contPaypal = document.getElementById('payment-paypal-container');
    
    if(method === 'stripe') {
        btnStripe.className = "flex-1 py-4 border border-amber-600/30 bg-amber-600/10 text-amber-500 text-[10px] uppercase tracking-widest font-black rounded-2xl transition-all";
        btnPaypal.className = "flex-1 py-4 border border-white/10 bg-white/5 text-white/50 text-[10px] uppercase tracking-widest font-black rounded-2xl transition-all hover:bg-white/10 hover:text-white";
        contStripe.classList.remove('hidden');
        contPaypal.classList.add('hidden');
    } else {
        btnPaypal.className = "flex-1 py-4 border border-amber-600/30 bg-amber-600/10 text-amber-500 text-[10px] uppercase tracking-widest font-black rounded-2xl transition-all";
        btnStripe.className = "flex-1 py-4 border border-white/10 bg-white/5 text-white/50 text-[10px] uppercase tracking-widest font-black rounded-2xl transition-all hover:bg-white/10 hover:text-white";
        contPaypal.classList.remove('hidden');
        contStripe.classList.add('hidden');
    }
}

async function confirmOrder(pi) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('confirm-order.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            csrf_token: csrfToken,
            email: document.getElementById('email').value,

            customerName: document.getElementById('name').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            postalCode: document.getElementById('postalCode').value,
            total: subtotal + shippingCost,
            paymentIntentId: pi
        })
    });
    const { trackingToken } = await res.json();
    window.location.href = 'track-order.php?token=' + trackingToken;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
