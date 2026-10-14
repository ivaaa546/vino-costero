/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                wine: '#5c1d2e',
                'wine-dark': '#431421',
                gold: '#b8860b',
                cream: '#f8f6f2',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
