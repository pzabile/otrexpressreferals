import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}"
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: "#F97316", // orange-500
          dark: "#C2410C",
          light: "#FB923C",
          accent: "#FACC15"
        },
        ink: {
          DEFAULT: "#0B0F14",
          soft: "#111827",
          mid: "#1F2937",
          line: "#374151"
        }
      },
      fontFamily: {
        display: ["Impact", "Oswald", "Arial Black", "sans-serif"],
        sans: ["Inter", "system-ui", "sans-serif"]
      },
      backgroundImage: {
        "hero-grid":
          "linear-gradient(rgba(11,15,20,0.85),rgba(11,15,20,0.9)),radial-gradient(circle at 20% 20%,rgba(249,115,22,0.25),transparent 40%),radial-gradient(circle at 80% 60%,rgba(250,204,21,0.15),transparent 40%)"
      }
    }
  },
  plugins: []
};

export default config;
