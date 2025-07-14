// resources/js/firebase.js

// Import fungsi yang Anda butuhkan dari SDK yang relevan
import { initializeApp } from "firebase/app";
// import { getAnalytics } from "firebase/analytics"; // Komen atau hapus jika tidak menggunakan Analytics
import { getAuth } from "firebase/auth";           // <-- PENTING: Import untuk Firebase Authentication
import { getDatabase } from "firebase/database";  // <-- PENTING: Import untuk Firebase Realtime Database

// Konfigurasi Firebase aplikasi web Anda
// Pastikan semua nilai ini sesuai dengan yang Anda dapatkan dari Firebase Console
const firebaseConfig = {
  apiKey: "AIzaSyB1Mcc_TZvyOS4Dr-NzcogJQ6wvevjQ4EQ",
  authDomain: "admin-dashboard-telkom.firebaseapp.com",
  databaseURL: "https://admin-dashboard-telkom-default-rtdb.asia-southeast1.firebasedatabase.app", // Pastikan ini URL Realtime Database Anda
  projectId: "admin-dashboard-telkom",
  storageBucket: "admin-dashboard-telkom.firebasestorage.app",
  messagingSenderId: "1055054419322",
  appId: "1:1055054419322:web:86b7a82bc0a63f27a072c3",
  // measurementId: "G-Y2TP6R0JBC" // Komen atau hapus jika tidak menggunakan Analytics
};

// Inisialisasi Firebase App
const app = initializeApp(firebaseConfig);

// Inisialisasi layanan Firebase yang akan Anda gunakan
// const analytics = getAnalytics(app); // Komen atau hapus jika tidak menggunakan Analytics
const auth = getAuth(app);         // <-- PENTING: Inisialisasi Auth
const db = getDatabase(app);       // <-- PENTING: Inisialisasi Realtime Database

// Ekspor layanan yang sudah diinisialisasi agar bisa diimpor di komponen Vue lainnya
export { app, auth, db }; // <-- PENTING: Ekspor 'db' dan 'auth'