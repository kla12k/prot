'use client';
import React from 'react';
import { motion } from 'framer-motion';
import Link from 'next/link';

const Home = () => {
  return (
    <div className="min-h-screen bg-cover bg-center flex items-center justify-center" style={{ backgroundImage: 'url(https://www.originfitness.com/media/magefan_blog/Dollar_Academy_WEB-01.jpg)' }}>
      <motion.div 
        className="max-w-5xl p-10 shadow-2xl rounded-2xl bg-white backdrop-blur-lg relative"
        initial={{ scale: 0.9, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        transition={{ duration: 0.8 }}>
        <div className="absolute inset-0 z-[-1] opacity-20 bg-center bg-cover" style={{ backgroundImage: 'url(https://www.originfitness.com/media/magefan_blog/Dollar_Academy_WEB-01.jpg)' }}></div>
        <div className="text-center">
          <h1 className="text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-600 mb-6">
            Achieve Your Best
          </h1>
          <p className="text-2xl text-gray-700 mb-8">
            Push your limits. Track your progress. Become unstoppable.
          </p>
          <Link href="/customer">
            <motion.button 
              className="text-white bg-gradient-to-r from-red-500 to-yellow-400 hover:from-red-600 hover:to-yellow-500 px-10 py-4 rounded-full text-lg shadow-xl"
              whileHover={{ scale: 1.1 }}
              whileTap={{ scale: 0.95 }}>
              Start Your Journey
            </motion.button>
          </Link>
        </div>
      </motion.div>
    </div>
  );
};

export default Home;