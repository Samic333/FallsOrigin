
import React, { useState } from 'react';
import { Mail, Phone, MapPin, Send, CheckCircle2 } from 'lucide-react';
import { api } from '../services/api';

const Contact: React.FC = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: ''
  });
  const [submitted, setSubmitted] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      await api.submitContactForm(formData);
      setSubmitted(true);
      setFormData({ name: '', email: '', subject: '', message: '' });
    } catch (err) {
      setError('Failed to send message. Please try again.');
      console.error('Contact form error:', err);
    } finally {
      setSubmitting(false);
    }
  };

  if (submitted) {
    return (
      <div className="py-40 bg-stone-950 text-center">
        <div className="max-w-md mx-auto px-4">
          <div className="w-20 h-20 bg-amber-600/10 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-8">
            <CheckCircle2 className="w-10 h-10" />
          </div>
          <h1 className="text-4xl font-serif font-bold mb-4">Message Received</h1>
          <p className="text-stone-500 mb-12">Our team will review your message and get back to you within 24 hours.</p>
          <button onClick={() => setSubmitted(false)} className="px-8 py-4 bg-white text-black font-bold uppercase text-xs tracking-widest">
            Send Another
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="py-20 bg-stone-950">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-20">
          <div>
            <span className="text-amber-600 font-bold uppercase tracking-[0.3em] text-xs mb-4 block">Get In Touch</span>
            <h1 className="text-5xl md:text-6xl font-serif font-bold mb-8">Connect With the Roastery.</h1>
            <p className="text-stone-500 text-lg mb-12 leading-relaxed">
              Have questions about our beans, your order, or wholesale opportunities? We're here to help you enjoy the perfect cup.
            </p>

            <div className="space-y-8">
              <div className="flex items-center space-x-6">
                <div className="w-12 h-12 bg-stone-900 rounded-full flex items-center justify-center border border-stone-800">
                  <Mail className="w-5 h-5 text-amber-600" />
                </div>
                <div>
                  <h4 className="text-xs font-bold uppercase tracking-widest text-stone-400">Email Us</h4>
                  <p className="text-white font-medium">hello@fallsorigin.coffee</p>
                </div>
              </div>
              <div className="flex items-center space-x-6">
                <div className="w-12 h-12 bg-stone-900 rounded-full flex items-center justify-center border border-stone-800">
                  <Phone className="w-5 h-5 text-amber-600" />
                </div>
                <div>
                  <h4 className="text-xs font-bold uppercase tracking-widest text-stone-400">Call Us</h4>
                  <p className="text-white font-medium">+1 (416) 555-0192</p>
                </div>
              </div>
              <div className="flex items-center space-x-6">
                <div className="w-12 h-12 bg-stone-900 rounded-full flex items-center justify-center border border-stone-800">
                  <MapPin className="w-5 h-5 text-amber-600" />
                </div>
                <div>
                  <h4 className="text-xs font-bold uppercase tracking-widest text-stone-400">Visit Us</h4>
                  <p className="text-white font-medium">Toronto, Ontario, Canada</p>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-stone-900 p-8 md:p-12 rounded-2xl border border-stone-800 shadow-2xl">
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className="text-[10px] font-bold uppercase tracking-widest text-stone-500 ml-1">Name</label>
                  <input
                    required
                    className="w-full bg-stone-950 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                    value={formData.name}
                    onChange={e => setFormData({ ...formData, name: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-[10px] font-bold uppercase tracking-widest text-stone-500 ml-1">Email</label>
                  <input
                    required
                    type="email"
                    className="w-full bg-stone-950 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                    value={formData.email}
                    onChange={e => setFormData({ ...formData, email: e.target.value })}
                  />
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-[10px] font-bold uppercase tracking-widest text-stone-500 ml-1">Subject</label>
                <input
                  required
                  className="w-full bg-stone-950 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all"
                  value={formData.subject}
                  onChange={e => setFormData({ ...formData, subject: e.target.value })}
                />
              </div>
              <div className="space-y-2">
                <label className="text-[10px] font-bold uppercase tracking-widest text-stone-500 ml-1">Message</label>
                <textarea
                  required
                  rows={5}
                  className="w-full bg-stone-950 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-white transition-all resize-none"
                  value={formData.message}
                  onChange={e => setFormData({ ...formData, message: e.target.value })}
                />
              </div>
              {error && (
                <div className="p-4 bg-red-500/10 border border-red-500/20 rounded text-red-500 text-sm">
                  {error}
                </div>
              )}
              <button
                disabled={submitting}
                className="w-full py-5 bg-white text-black font-bold uppercase text-xs tracking-[0.2em] hover:bg-amber-600 hover:text-white transition-all flex items-center justify-center group disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {submitting ? 'Sending...' : 'Send Message'} <Send className="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Contact;
