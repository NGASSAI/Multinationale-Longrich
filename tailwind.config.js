/** @type {import("tailwindcss").Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        emerald: {
          DEFAULT: "#0F3D2E",
          light: "#1A5240",
          dark: "#0A2B20",
        },
        gold: {
          DEFAULT: "#C89B3C",
          light: "#D9B45E",
          dark: "#A87F2C",
        },
        ivory: "#FAF7F0",
        charcoal: "#1F1D1A",
        sage: "#E8EDE4",
      },
      fontFamily: {
        display: ["Fraunces", "serif"],
        sans: ["Satoshi", "ui-sans-serif", "system-ui", "sans-serif"],
      },
      borderRadius: {
        DEFAULT: "0.375rem",
      },
      animation: {
        "fade-in": "fadeIn 0.4s ease-out",
        "slide-up": "slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1)",
        "pulse-once": "pulseOnce 0.6s ease-out",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" },
        },
        slideUp: {
          "0%": { opacity: "0", transform: "translateY(24px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        pulseOnce: {
          "0%": { transform: "scale(1)" },
          "40%": { transform: "scale(1.15)" },
          "100%": { transform: "scale(1)" },
        },
      },
    },
  },
  plugins: [],
};
