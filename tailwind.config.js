/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'Poppins', 'sans-serif'],
      },
      colors: {
        'dark-blue': '#1E2A45',
        'midnight': '#121827',
        'accent-blue': '#3B82F6',
        'accent-hover': '#2563EB'
      },
      boxShadow: {
        'glow': '0 0 20px rgba(59, 130, 246, 0.25)'
      }
    }
  },
  plugins: [],
}
