import React from 'react';

export const Logo: React.FC<{ className?: string }> = ({ className = "w-8 h-8" }) => (
  <svg 
    viewBox="0 0 100 100" 
    fill="currentColor" 
    className={className}
    xmlns="http://www.w3.org/2000/svg"
  >
    {/* 5 vertical lines mirroring the packaging logo exactly */}
    <path d="M48 5h4v55c0 10-1.5 20-3 30h-1c-1.5-10-3-20-3-30V5z" />
    <path d="M36 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
    <path d="M60.5 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
    <path d="M25 28h3v35c0 6-1.5 12-3 18h-1c-1.5-6-3-12-3-18V28z" opacity="0.6" />
    <path d="M72 28h3v35c0 6-1.5 12-3 18h-1c-1.5-6-3-12-3-18V28z" opacity="0.6" />
  </svg>
);

export const BrandTitle: React.FC<{ className?: string }> = ({ className = "" }) => (
  <div className={`flex flex-col items-center text-center ${className}`}>
    <span className="text-2xl font-bold tracking-[0.25em] uppercase leading-none">Falls Origin</span>
    <div className="flex items-center w-full mt-2">
      <div className="h-[1px] flex-grow bg-white/20"></div>
      <span className="text-[10px] font-bold tracking-[0.5em] uppercase px-3 opacity-60">Coffee</span>
      <div className="h-[1px] flex-grow bg-white/20"></div>
    </div>
  </div>
);
