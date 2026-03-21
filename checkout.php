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
    $qty = $_SESSION['cart'][$p['id']];
    $subtotal += $p['price'] * $qty;
    $cartItems[] = ['product' => $p, 'quantity' => $qty];
}

// Fixed shipping calculation for simplicity in this reorganization
$shippingCost = 15.00;
$total = $subtotal + $shippingCost;
?>

<div class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
            <!-- Form Steps -->
            <div class="lg:col-span-2 space-y-12">
                <div id="step-1" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] transition-all duration-700">
                    <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">I. Logistics Identity</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div><label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Email</label><input type="email" id="email" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs"></div>
                        <div><label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Full Identity</label><input type="text" id="name" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs"></div>
                        <div class="md:col-span-2"><label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Secure Address</label><input type="text" id="address" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs"></div>
                        <div><label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">City</label><input type="text" id="city" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs"></div>
                        <div><label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Postal/Zip</label><input type="text" id="postalCode" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs"></div>
                    </div>
                    <button onclick="calculateDelivery()" class="mt-12 w-full py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] rounded-full hover:bg-amber-600 hover:text-white transition-all">Authenticate Logistics</button>
                </div>

                <div id="step-2" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] opacity-20 pointer-events-none transition-all duration-700">
                    <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">II. Fulfillment Strategy</h3>
                    <div id="delivery-info" class="text-xs border border-white/5 p-8 rounded-[2.5rem] bg-white/[0.01] mb-8 uppercase tracking-widest text-white/40 leading-relaxed italic">Logistics pending authentication...</div>
                    <button onclick="goToPaymentStep()" class="w-full py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] rounded-full">Confirm Strategy</button>
                </div>

                <div id="step-3" class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] opacity-20 pointer-events-none transition-all duration-700">
                    <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">III. Financial Settlement</h3>
                    <div id="payment-element" class="mb-10"></div>
                    <button id="submit-payment" class="w-full py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] rounded-full shadow-2xl">Complete Transaction</button>
                    <div id="payment-message" class="hidden mt-6 p-4 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest rounded-2xl text-center"></div>
                </div>
            </div>

            <!-- Summary -->
            <div class="space-y-12">
                <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] sticky top-32">
                    <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10 italic">Summary Ledger</h3>
                    <div class="space-y-6 mb-12">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="flex justify-between items-center text-[11px] font-black uppercase tracking-widest text-white/40">
                            <span><?php echo e($item['product']['name']); ?> (<?php echo $item['quantity']; ?>)</span>
                            <span class="text-white">$<?php echo number_format($item['product']['price'] * $item['quantity'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t border-white/5 pt-8 space-y-4">
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-white/20"><span>Base Value</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-white/20"><span>Logistics</span><span id="shipping-display">$<?php echo number_format($shippingCost, 2); ?></span></div>
                        <div class="flex justify-between text-xl font-serif font-bold text-white pt-4 italic"><span>Consolidated</span><span id="total-display">$<?php echo number_format($total, 2); ?></span></div>
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

    stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
    elements = stripe.elements({ clientSecret, appearance: { theme: 'night' } });
    paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    goToStep(3);

    document.getElementById('submit-payment').onclick = async () => {
        const { error: stripeError, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: window.location.origin + '/track-order.php' },
            redirect: 'if_required'
        });

        if (paymentIntent && paymentIntent.status === 'succeeded') {
            await confirmOrder(paymentIntent.id);
        } else if (stripeError) {
            document.getElementById('payment-message').innerText = stripeError.message;
            document.getElementById('payment-message').classList.remove('hidden');
        }
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

<?php include __DIR__ . '/includes/footer.php'; ?>
