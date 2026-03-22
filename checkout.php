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
        <header class="mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic"><?php echo __('transactional_transparency'); ?></h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('checkout'); ?></h1>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
            <!-- Form Steps -->
            <div class="lg:col-span-2 space-y-12">
                <div id="step-1" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] transition-all duration-700 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-white mb-10">I. <?php echo __('logistics_intel'); ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('electronic_mail'); ?></label><input type="email" id="email" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('identity'); ?></label><input type="text" id="name" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div class="md:col-span-2"><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Secure Address</label><input type="text" id="address" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">City</label><input type="text" id="city" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                        <div><label class="text-[10px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Postal/Zip</label><input type="text" id="postalCode" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs focus:border-amber-600 outline-none transition-all"></div>
                    </div>
                    <button onclick="calculateDelivery()" class="btn btn-gold mt-12 w-full py-6 uppercase text-[11px] tracking-[0.4em] shadow-xl">Authenticate Logistics</button>
                </div>

                <div id="step-2" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] opacity-20 pointer-events-none transition-all duration-700 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-white mb-10">II. Fulfillment Strategy</h3>
                    <div id="delivery-info" class="text-[10px] border border-white/5 p-8 rounded-[2.5rem] bg-white/[0.01] mb-8 uppercase tracking-[0.2em] text-white/40 leading-relaxed italic font-bold">Logistics pending authentication...</div>
                    <button onclick="goToPaymentStep()" class="btn btn-gold w-full py-6 uppercase text-[11px] tracking-[0.4em] shadow-xl">Confirm Strategy</button>
                </div>

                <div id="step-3" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] opacity-20 pointer-events-none transition-all duration-700 shadow-2xl">
                    <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-white mb-10">III. Financial Settlement</h3>
                    <div id="payment-element" class="mb-10"></div>
                    <button id="submit-payment" class="btn btn-gold w-full py-6 uppercase text-[11px] tracking-[0.4em] shadow-2xl">Complete Transaction</button>
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
    if (error) { alert(error); return; }

    stripe = Stripe('<?php echo STRIPE_PUBLIC_KEY; ?>');
    elements = stripe.elements({ clientSecret, appearance: { theme: 'night' } });
    paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    goToStep(3);

    document.getElementById('submit-payment').onclick = async () => {
        // MOCK PAYMENTS FOR DEV PHASE:
        // Normally you'dw wait stripe.confirmPayment(...)
        // Since we lack API keys locally, simulate immediate success.
        
        const mockPaymentIntentId = 'pi_simulated_' + Math.floor(Math.random() * 99999);
        await confirmOrder(mockPaymentIntentId);
    };
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
