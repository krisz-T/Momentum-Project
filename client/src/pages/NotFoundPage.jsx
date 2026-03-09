import React from 'react';
import { Link } from 'react-router-dom';

const NotFoundPage = () => {
  return (
    <div style={{ textAlign: 'center', marginTop: '100px' }}>
      <h1>404 - Page Not Found</h1>
      <p>Oops! The page you are looking for does not exist.</p>
      <Link to="/" style={{ color: '#646cff', textDecoration: 'underline' }}>
        Go Back Home
      </Link>
    </div>
  );
};

export default NotFoundPage;
