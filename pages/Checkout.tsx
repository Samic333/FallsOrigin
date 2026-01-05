
import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { CreditCard, Truck, MapPin, CheckCircle2, AlertCircle } from 'lucide-react';
import { CartItem, DeliveryMethod } from '../types';
import { api } from '../services/api';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement, useStripe, useElements } from '@stripe/react-stripe-js';

// Initialize Stripe
const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY || '');

interface CheckoutProps {
  items: CartItem[];
  onClearCart: () => void;
}

// Payment Form Component
const PaymentForm: React.FC<{
  clientSecret: string;
  onSuccess: (paymentIntentId: string) => void;
  onBack: () => void;
  total: number;
}> = ({ clientSecret, onSuccess, onBack, total }) => {
  const stripe = useStripe();
  const elements = useElements();
  const [message, setMessage] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (!stripe) return;

    const clientSecret = new URLSearchParams(window.location.search).get(
      "payment_intent_client_secret"
    );

    if (!clientSecret) return;

    stripe.retrievePaymentIntent(clientSecret).then(({ paymentIntent }) => {
      switch (paymentIntent?.status) {
        case "succeeded":
          setMessage("Payment succeeded!");
          break;
        case "processing":
          setMessage("Your payment is processing.");
          break;
        case "requires_payment_method":
          setMessage("Your payment was not successful, please try again.");
          break;
        default:
          setMessage("Something went wrong.");
          break;
      }
    });
  }, [stripe]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!stripe || !elements) return;

    setIsLoading(true);

    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: window.location.origin + '/shop', // Fallback
      },
      redirect: 'if_required',
    });

    if (error) {
      setMessage(error.message || "An unexpected error occurred.");
      setIsLoading(false);
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
      onSuccess(paymentIntent.id);
    } else {
      setMessage("Unexpected payment status.");
      setIsLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div className="bg-stone-900 border border-stone-800 p-6 rounded-lg">
        <PaymentElement id="payment-element" options={{ layout: "tabs" }} />
      </div>

      {message && (
        <div className="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded text-sm flex items-center">
          <AlertCircle className="w-4 h-4 mr-2" />
          {message}
        </div>
      )}

      <div className="flex gap-4">
        <button
          type="button"
          onClick={onBack}
          disabled={isLoading}
          className="w-1/3 py-5 bg-transparent border border-stone-700 text-stone-400 font-bold uppercase text-xs tracking-[0.2em] hover:bg-stone-900 transition-all rounded"
        >
          Back
        </button>
        <button
          type="submit"
          disabled={isLoading || !stripe || !elements}
          className="flex-grow py-5 bg-amber-600 text-white font-bold uppercase text-xs tracking-[0.2em] hover:bg-amber-700 transition-all rounded shadow-lg shadow-amber-600/20 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isLoading ? "Processing..." : `Pay $${total.toFixed(2)}`}
        </button>
      </div>
    </form>
  );
};

const Checkout: React.FC<CheckoutProps> = ({ items, onClearCart }) => {
  const navigate = useNavigate();
  const [step, setStep] = useState<1 | 2 | 3>(1);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Form Data
  const [formData, setFormData] = useState({
    email: '',
    name: '',
    address: '',
    city: '',
    province: 'Ontario',
    postalCode: ''
  });

  // Delivery State
  const [deliveryMethod, setDeliveryMethod] = useState<DeliveryMethod | null>(null);
  const [shippingCost, setShippingCost] = useState(0);

  // Payment State
  const [clientSecret, setClientSecret] = useState('');

  const subtotal = items.reduce((acc, item) => acc + item.product.price * item.quantity, 0);
  const total = subtotal + shippingCost;

  useEffect(() => {
    if (items.length === 0) navigate('/shop');
  }, [items, navigate]);

  // Step 1: Address & Delivery Calculation
  const handleCalculateDelivery = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const result = await api.calculateDelivery({
        address: formData.address,
        city: formData.city,
        province: formData.province,
        postalCode: formData.postalCode
      });

      setDeliveryMethod(result.deliveryMethod);
      setShippingCost(result.shippingCost);
      setStep(2);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to calculate delivery. Please check the address.');
    } finally {
      setLoading(false);
    }
  };

  // Step 2: Create Payment Intent
  const handleProceedToPayment = async () => {
    setLoading(true);
    setError(null);

    try {
      const { clientSecret } = await api.createPaymentIntent({
        amount: total,
        email: formData.email,
        customerName: formData.name
      });

      setClientSecret(clientSecret);
      setStep(3);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Failed to initialize payment.');
    } finally {
      setLoading(false);
    }
  };

  // Step 3: Confirm Order
  const handlePaymentSuccess = async (paymentIntentId: string) => {
    setLoading(true);
    try {
      const { orderId, trackingToken } = await api.confirmOrder({
        paymentIntentId,
        email: formData.email,
        customerName: formData.name,
        address: formData.address,
        city: formData.city,
        province: formData.province,
        postalCode: formData.postalCode,
        items,
        total,
        deliveryMethod
      });

      onClearCart();
      navigate(`/track?token=${trackingToken}`); // Use secure token
    } catch (err: any) {
      setError(err.response?.data?.error || 'Payment successful but failed to confirm order. Please contact support.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="py-20 bg-stone-950 min-h-screen">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-4xl font-serif font-bold mb-12">Secure Checkout</h1>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-16">
          <div className="space-y-12">

            {/* Step 1: Contact & Shipping */}
            <section className={step !== 1 ? 'opacity-50 pointer-events-none grayscale transition-all' : ''}>
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-xs font-bold uppercase tracking-widest text-amber-600 flex items-center">
                  <span className="w-6 h-6 rounded-full border border-amber-600 flex items-center justify-center mr-3">1</span>
                  Shipping Details
                </h3>
                {step > 1 && (
                  <button onClick={() => setStep(1)} className="text-[10px] uppercase font-bold text-amber-600 underline">Edit</button>
                )}
              </div>

              <form id="address-form" onSubmit={handleCalculateDelivery} className="space-y-6">
                <input
                  required
                  type="email"
                  placeholder="Email Address"
                  className="w-full bg-stone-900 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                  value={formData.email}
                  onChange={e => setFormData({ ...formData, email: e.target.value })}
                />
                <input
                  required
                  placeholder="Full Name"
                  className="w-full bg-stone-900 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                  value={formData.name}
                  onChange={e => setFormData({ ...formData, name: e.target.value })}
                />
                <input
                  required
                  placeholder="Address"
                  className="w-full bg-stone-900 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                  value={formData.address}
                  onChange={e => setFormData({ ...formData, address: e.target.value })}
                />
                <div className="grid grid-cols-2 gap-4">
                  <input
                    required
                    placeholder="City"
                    className="bg-stone-900 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                    value={formData.city}
                    onChange={e => setFormData({ ...formData, city: e.target.value })}
                  />
                  <input
                    required
                    placeholder="Postal Code"
                    className="bg-stone-900 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all uppercase"
                    value={formData.postalCode}
                    onChange={e => setFormData({ ...formData, postalCode: e.target.value.toUpperCase() })}
                  />
                </div>

                {error && step === 1 && (
                  <div className="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded text-sm">
                    {error}
                  </div>
                )}

                {step === 1 && (
                  <button
                    type="submit"
                    disabled={loading}
                    className="w-full py-5 bg-white text-black font-bold uppercase text-xs tracking-[0.2em] hover:bg-amber-600 hover:text-white transition-all disabled:opacity-50"
                  >
                    {loading ? 'Calculating...' : 'Calculate Shipping'}
                  </button>
                )}
              </form>
            </section>

            {/* Step 2: Delivery Method Confirmation */}
            {step >= 2 && (
              <section className={`animate-in fade-in slide-in-from-bottom-4 duration-500 ${step !== 2 ? 'opacity-50 pointer-events-none grayscale' : ''}`}>
                <div className="flex justify-between items-center mb-6">
                  <h3 className="text-xs font-bold uppercase tracking-widest text-amber-600 flex items-center">
                    <span className="w-6 h-6 rounded-full border border-amber-600 flex items-center justify-center mr-3">2</span>
                    Delivery Method
                  </h3>
                  {step > 2 && (
                    <button onClick={() => setStep(2)} className="text-[10px] uppercase font-bold text-amber-600 underline">Edit</button>
                  )}
                </div>

                <div className="bg-stone-900 border border-amber-600/30 p-6 rounded-lg flex items-center justify-between mb-8">
                  <div className="flex items-center">
                    {deliveryMethod === DeliveryMethod.LOCAL ? (
                      <MapPin className="text-amber-600 w-6 h-6 mr-4" />
                    ) : (
                      <Truck className="text-amber-600 w-6 h-6 mr-4" />
                    )}
                    <div>
                      <p className="font-bold text-sm uppercase tracking-widest">{deliveryMethod}</p>
                      <p className="text-stone-500 text-xs mt-1">
                        {deliveryMethod === DeliveryMethod.LOCAL
                          ? 'Falls Origin Local Delivery (GTA)'
                          : 'Canada Post Expedited Parcel'}
                      </p>
                    </div>
                  </div>
                  <span className="font-bold font-serif text-lg">${shippingCost.toFixed(2)}</span>
                </div>

                {error && step === 2 && (
                  <div className="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded text-sm mb-4">
                    {error}
                  </div>
                )}

                {step === 2 && (
                  <button
                    onClick={handleProceedToPayment}
                    disabled={loading}
                    className="w-full py-5 bg-white text-black font-bold uppercase text-xs tracking-[0.2em] hover:bg-amber-600 hover:text-white transition-all disabled:opacity-50"
                  >
                    {loading ? 'Initializing Payment...' : 'Proceed to Payment'}
                  </button>
                )}
              </section>
            )}

            {/* Step 3: Payment */}
            {step === 3 && clientSecret && (
              <section className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <h3 className="text-xs font-bold uppercase tracking-widest text-amber-600 mb-6 flex items-center">
                  <span className="w-6 h-6 rounded-full border border-amber-600 flex items-center justify-center mr-3">3</span>
                  Payment
                </h3>

                <div className="bg-stone-900 border border-stone-800 p-1 rounded-xl">
                  {import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY ? (
                    <Elements stripe={stripePromise} options={{
                      clientSecret,
                      appearance: {
                        theme: 'night',
                        variables: {
                          colorPrimary: '#d97706',
                          colorBackground: '#1c1917',
                          colorText: '#ffffff',
                          colorDanger: '#ef4444',
                          fontFamily: 'ui-sans-serif, system-ui, sans-serif',
                          borderRadius: '4px',
                        },
                      }
                    }}>
                      <PaymentForm
                        clientSecret={clientSecret}
                        onSuccess={handlePaymentSuccess}
                        onBack={() => setStep(2)}
                        total={total}
                      />
                    </Elements>
                  ) : (
                    <div className="p-8 text-center">
                      <p className="text-red-500 font-bold mb-2">Stripe Configuration Missing</p>
                      <p className="text-stone-500 text-sm">Please set VITE_STRIPE_PUBLISHABLE_KEY in your environment variables.</p>
                      <button onClick={() => setStep(2)} className="mt-4 text-white underline">Go Back</button>
                    </div>
                  )}
                </div>
              </section>
            )}
          </div>

          {/* Order Summary */}
          <div className="bg-stone-900 p-8 rounded-2xl h-fit sticky top-24 border border-stone-800">
            <h3 className="text-xl font-serif font-bold mb-8">Order Summary</h3>
            <div className="space-y-6 mb-8 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
              {items.map(item => (
                <div key={item.product.id} className="flex justify-between items-center group">
                  <div className="flex items-center">
                    <div className="w-12 h-12 bg-stone-800 rounded mr-4 relative overflow-hidden">
                      <img src={item.product.image} className="w-full h-full object-cover" />
                      <span className="absolute -top-2 -right-2 bg-white text-black text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-lg">
                        {item.quantity}
                      </span>
                    </div>
                    <div>
                      <span className="text-xs text-white font-bold uppercase tracking-tight block">{item.product.name}</span>
                      <span className="text-[10px] text-stone-500 uppercase tracking-widest">{item.product.weight}</span>
                    </div>
                  </div>
                  <span className="font-bold text-sm text-stone-300 ml-4">${(item.product.price * item.quantity).toFixed(2)}</span>
                </div>
              ))}
            </div>
            <div className="space-y-4 border-t border-stone-800 pt-8">
              <div className="flex justify-between text-stone-500 text-sm font-medium">
                <span>Subtotal</span>
                <span>${subtotal.toFixed(2)}</span>
              </div>
              <div className="flex justify-between text-stone-500 text-sm font-medium">
                <span>Shipping</span>
                <span>{step >= 2 ? `$${shippingCost.toFixed(2)}` : 'Calculated next'}</span>
              </div>
              <div className="flex justify-between text-white text-xl font-serif font-bold pt-4 border-t border-stone-800 mt-4">
                <span>Total</span>
                <span>${total.toFixed(2)} <span className="text-xs font-sans font-normal text-stone-500 ml-1">CAD</span></span>
              </div>
            </div>
            <div className="mt-8 flex items-center justify-center space-x-2 opacity-30 grayscale">
              <CreditCard className="w-5 h-5" />
              <span className="text-[10px] uppercase font-bold tracking-widest">Secured by Stripe</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Checkout;
