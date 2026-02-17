import { jsx } from "@app/html-jsx"

export default function Styles() {
  return (
    <>
      <script src="/s/static/lib/tailwind.3.4.16.min.js"></script>
      <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
      <link href="/s/static/lib/fontawesome/6.7.2/css/all.min.css" rel="stylesheet" />
      <script>{`
        tailwind.config = {
          theme: {
            extend: {
              fontFamily: {
                'serif': ['Playfair Display', 'serif'],
                'sans': ['Inter', 'sans-serif'],
              },
              colors: {
                'primary': '#8B4513',
                'primary-light': '#A0522D',
                'primary-dark': '#654321',
                'gold': '#D4AF37',
                'gold-light': '#F4D03F',
                'cream': '#FAF8F5',
                'cream-dark': '#F5F0EB',
                'warm-gray': '#9A8B7A',
                'dark': '#2C2416',
              }
            }
          }
        }
      `}</script>
      <style>{`
        body {
          font-family: 'Inter', sans-serif;
          background-color: #FAF8F5;
        }
        .font-serif {
          font-family: 'Playfair Display', serif;
        }
        .gradient-gold {
          background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 50%, #D4AF37 100%);
        }
        .text-shadow {
          text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hover-lift {
          transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
          transform: translateY(-4px);
          box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .pattern-bg {
          background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23D4AF37' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
      `}</style>
    </>
  )
}
