import React, { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { Shield, Truck, RefreshCcw, FileText, ChevronRight } from 'lucide-react';

type PolicySection = 'shipping' | 'refunds' | 'terms';

const Policies: React.FC = () => {
  const [activeSection, setActiveSection] = useState<PolicySection>('shipping');
  const location = useLocation();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [activeSection]);

  const sections = [
    { id: 'shipping', label: 'Shipping & Delivery', icon: Truck },
    { id: 'refunds', label: 'Returns & Refunds', icon: RefreshCcw },
    { id: 'terms', label: 'Terms of Service', icon: FileText },
  ];

  return (
    <div className="bg-[#050505] min-h-screen pb-32">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div className="flex flex-col md:flex-row gap-20">
          
          {/* Sidebar Navigation */}
          <aside className="w-full md:w-80 flex-shrink-0">
            <div className="sticky top-32 space-y-8">
              <div>
                <div className="flex items-center space-x-3 mb-6">
                  <Shield className="w-5 h-5 text-amber-600" />
                  <span className="text-amber-600 font-black uppercase tracking-[0.4em] text-[10px]">Legal Framework</span>
                </div>
                <h1 className="text-4xl font-serif font-bold text-white tracking-tight uppercase">Policies</h1>
              </div>

              <nav className="space-y-2">
                {sections.map((section) => (
                  <button
                    key={section.id}
                    onClick={() => setActiveSection(section.id as PolicySection)}
                    className={`w-full flex items-center justify-between px-6 py-5 rounded-2xl transition-all group ${
                      activeSection === section.id 
                        ? 'bg-white text-black shadow-2xl' 
                        : 'text-white/40 hover:text-white hover:bg-white/5'
                    }`}
                  >
                    <div className="flex items-center">
                      <section.icon className={`w-4 h-4 mr-4 ${activeSection === section.id ? 'text-black' : 'text-white/20 group-hover:text-amber-600'}`} />
                      <span className="text-[11px] font-black uppercase tracking-widest">{section.label}</span>
                    </div>
                    <ChevronRight className={`w-4 h-4 transition-transform ${activeSection === section.id ? 'translate-x-0' : '-translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0'}`} />
                  </button>
                ))}
              </nav>

              <div className="p-8 bg-white/5 rounded-[2rem] border border-white/5">
                <p className="text-[9px] text-white/20 font-black uppercase tracking-[0.3em] mb-4">Support Concierge</p>
                <p className="text-xs text-white/40 leading-relaxed mb-6">Need clarification on our legal framework? Our team is available for direct inquiry.</p>
                <a href="mailto:concierge@fallsorigin.coffee" className="text-[10px] font-black uppercase tracking-widest text-amber-600 hover:text-white transition-colors underline underline-offset-8">Contact Support</a>
              </div>
            </div>
          </aside>

          {/* Policy Content */}
          <main className="flex-grow max-w-3xl">
            <div className="animate-in fade-in slide-in-from-bottom-4 duration-700">
              {activeSection === 'shipping' && (
                <div className="space-y-12">
                  <section>
                    <h2 className="text-3xl font-serif font-bold text-white mb-8">Shipping & Delivery</h2>
                    <p className="text-white/50 leading-relaxed mb-6">
                      At Falls Origin Coffee, we roast in small batches to ensure maximum flavor clarity. Our shipping infrastructure is designed to bridge the gap between our Toronto roastery and your doorstep with minimal delay.
                    </p>
                  </section>

                  <section className="space-y-6">
                    <h3 className="text-[10px] font-black uppercase tracking-[0.4em] text-amber-600">Local Delivery (GTA)</h3>
                    <p className="text-white/40 text-sm leading-relaxed">
                      We offer specialized local courier delivery for the Greater Toronto Area. Orders placed before 10:00 AM EST are eligible for next-day delivery, contingent on roast schedules.
                    </p>
                    <ul className="space-y-4 text-xs text-white/40">
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Free local delivery on orders over $50.00 CAD.</span></li>
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Flat rate of $5.00 CAD for GTA orders under $50.00 CAD.</span></li>
                    </ul>
                  </section>

                  <section className="space-y-6">
                    <h3 className="text-[10px] font-black uppercase tracking-[0.4em] text-amber-600">Postal Shipping (Canada-wide)</h3>
                    <p className="text-white/40 text-sm leading-relaxed">
                      For patrons outside of the GTA, we utilize tracked postal services (Canada Post & UPS). All coffee is shipped in industrial-grade, valve-sealed bags within robust cardboard housing to prevent crushing.
                    </p>
                    <ul className="space-y-4 text-xs text-white/40">
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Standard Shipping: 3-7 business days depending on province.</span></li>
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Expedited options are available at checkout.</span></li>
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Tracking numbers are issued via email once the roast has been processed.</span></li>
                    </ul>
                  </section>

                  <section className="pt-10 border-t border-white/5">
                    <p className="text-[10px] text-white/20 uppercase font-bold tracking-widest italic">Last Updated: October 2024</p>
                  </section>
                </div>
              )}

              {activeSection === 'refunds' && (
                <div className="space-y-12">
                  <section>
                    <h2 className="text-3xl font-serif font-bold text-white mb-8">Returns & Refunds</h2>
                    <p className="text-white/50 leading-relaxed mb-6">
                      We stand by the quality of our heirloom beans. However, as coffee is a perishable food product, our return policy is strict to maintain health and safety standards.
                    </p>
                  </section>

                  <section className="space-y-6">
                    <h3 className="text-[10px] font-black uppercase tracking-[0.4em] text-amber-600">Perishable Goods (Coffee)</h3>
                    <p className="text-white/40 text-sm leading-relaxed">
                      Freshly roasted coffee cannot be returned once the seal has been broken or the product has left our controlled environment. If you are dissatisfied with your roast, please contact us within 7 days of delivery for a consultation.
                    </p>
                    <ul className="space-y-4 text-xs text-white/40">
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Damaged Goods: If your package arrives compromised, provide photographic evidence within 24 hours for a priority replacement.</span></li>
                      <li className="flex items-start"><span className="text-amber-600 mr-4">•</span> <span>Incorrect Order: We will rectify any fulfillment errors at no cost to the customer.</span></li>
                    </ul>
                  </section>

                  <section className="space-y-6">
                    <h3 className="text-[10px] font-black uppercase tracking-[0.4em] text-amber-600">Hardware & Equipment</h3>
                    <p className="text-white/40 text-sm leading-relaxed">
                      Brewing equipment (scales, drippers, grinders) may be returned within 30 days of purchase if unused and in original packaging. A 15% restocking fee may apply to high-value electronic equipment.
                    </p>
                  </section>

                  <section className="pt-10 border-t border-white/5">
                    <p className="text-[10px] text-white/20 uppercase font-bold tracking-widest italic">Last Updated: October 2024</p>
                  </section>
                </div>
              )}

              {activeSection === 'terms' && (
                <div className="space-y-12 text-sm text-white/40 leading-relaxed">
                  <section>
                    <h2 className="text-3xl font-serif font-bold text-white mb-8 uppercase tracking-tight">Terms of Service</h2>
                    <p className="text-white/50 mb-8">By accessing the Falls Origin Coffee portal, you agree to be bound by the following high-standard protocols.</p>
                  </section>

                  <div className="space-y-10">
                    <section>
                      <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-white mb-4">1. Use of Service</h4>
                      <p>The Falls Origin Coffee website is for personal and wholesale commercial use. Users must be of legal age to enter into contracts in their jurisdiction of residence (Ontario, Canada or otherwise).</p>
                    </section>

                    <section>
                      <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-white mb-4">2. Intellectual Property</h4>
                      <p>All brand assets, including the "Falls Origin" logo, typography, and product photography, are the exclusive property of Falls Origin Coffee Roasters. Unauthorized reproduction for commercial gain is strictly prohibited.</p>
                    </section>

                    <section>
                      <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-white mb-4">3. Accuracy of Information</h4>
                      <p>We strive for absolute accuracy in our origin descriptions. However, seasonal agricultural variations may occur in bean size or visual density. We reserve the right to modify prices or roast availability without prior notice.</p>
                    </section>

                    <section>
                      <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-white mb-4">4. Governing Law</h4>
                      <p>These terms are governed by the laws of the Province of Ontario and the federal laws of Canada applicable therein.</p>
                    </section>
                  </div>

                  <section className="pt-10 border-t border-white/5">
                    <p className="text-[10px] text-white/20 uppercase font-bold tracking-widest italic">Last Updated: October 2024</p>
                  </section>
                </div>
              )}
            </div>
          </main>
        </div>
      </div>
    </div>
  );
};

export default Policies;