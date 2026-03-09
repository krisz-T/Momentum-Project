import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { supabase } from '../supabaseClient';
import { FaTachometerAlt, FaUser, FaClipboardList, FaSignOutAlt, FaDumbbell } from 'react-icons/fa';

const Header = () => {
  const { session, userProfile } = useAuth();

  const handleLogout = async () => {
    await supabase.auth.signOut();
  };

  return (
    <header className="app-header">
      <div className="header-left">
        <Link to="/" className="brand-link">
          <FaDumbbell />
          <span>Momentum</span>
        </Link>
      </div>
      <div className="header-right">
        {userProfile?.role === 'Admin' && (
          <Link to="/admin" className="icon-link" title="Admin Dashboard">
            <FaTachometerAlt />
          </Link>
        )}
        <Link to="/plans" className="icon-link" title="Training Plans">
          <FaClipboardList />
        </Link>
        <Link to="/profile" className="icon-link" title="My Profile">
          <FaUser />
        </Link>
        <span style={{ color: '#888', margin: '0 0.5rem' }}>|</span>
        <button onClick={handleLogout} className="icon-button" title="Logout" style={{ padding: '0.4rem 0.8rem', fontSize: '0.9rem', backgroundColor: 'transparent', border: '1px solid rgba(255,107,107,0.3)', color: '#ff6b6b' }}>
          <FaSignOutAlt />
          <span>Logout</span>
        </button>
      </div>
    </header>
  );
};

export default Header;