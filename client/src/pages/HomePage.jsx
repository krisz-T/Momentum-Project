import { useState, useEffect, useCallback } from 'react';
import WorkoutForm from '../components/WorkoutForm';
import { useAuth } from '../contexts/AuthContext';
import { FaTrophy } from 'react-icons/fa';

const HomePage = () => {
  const { session, userProfile } = useAuth();
  const [leaderboard, setLeaderboard] = useState([]);
  const [timeframe, setTimeframe] = useState('all-time');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);

  const fetchLeaderboard = useCallback(async () => {
    setLoading(true);
    try {
      const apiUrl = `${import.meta.env.VITE_API_BASE_URL}/api/leaderboard?timeframe=${timeframe}`;
      const response = await fetch(apiUrl);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const data = await response.json();
      setLeaderboard(data);
    } catch (e) {
      setError(e.message);
      console.error("Failed to fetch leaderboard:", e);
    } finally {
      setLoading(false);
    }
  }, [timeframe]);

  useEffect(() => {
    if (session) fetchLeaderboard();
  }, [session, refreshKey, fetchLeaderboard, timeframe]);

  const handleWorkoutLogged = () => {
    setRefreshKey(prevKey => prevKey + 1);
  };

  return (
    <>
      <WorkoutForm onWorkoutLogged={handleWorkoutLogged} />
      <hr />
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '2rem' }}>
        <h1 style={{ fontSize: '2.5rem', fontWeight: '700', margin: 0 }}>Momentum Leaderboard</h1>
        <select 
          value={timeframe} 
          onChange={(e) => setTimeframe(e.target.value)}
          style={{ padding: '0.5rem', borderRadius: '8px', backgroundColor: '#1a1a1a', color: '#fff', border: '1px solid #333' }}
        >
          <option value="all-time">All Time</option>
          <option value="month">This Month</option>
          <option value="week">This Week</option>
        </select>
      </div>

      {loading && <p>Loading...</p>}
      {error && <p>Error: {error}</p>}
      {!loading && !error && (
        <table>
          <thead>
            <tr>
              <th>Rank</th>
              <th>Name</th>
              <th>XP</th>
            </tr>
          </thead>
          <tbody>
            {leaderboard.length === 0 ? (
              <tr><td colSpan="3" style={{ textAlign: 'center' }}>No workouts logged in this timeframe yet!</td></tr>
            ) : (
              leaderboard.map((user, index) => {
                const isCurrentUser = userProfile && user.id === userProfile.id;
                return (
                  <tr key={user.id} style={{ 
                    backgroundColor: isCurrentUser ? 'rgba(100, 108, 255, 0.15)' : 'transparent',
                  }}>
                    <td style={{ fontWeight: isCurrentUser ? 'bold' : 'normal', borderLeft: isCurrentUser ? '4px solid #646cff' : 'none' }}>
                      {index + 1}
                      {index === 0 && <FaTrophy style={{ color: 'gold', marginLeft: '0.5rem' }}/>}
                    </td>
                    <td style={{ fontWeight: isCurrentUser ? 'bold' : 'normal' }}>
                      {user.name} {isCurrentUser && <span style={{ fontSize: '0.8rem', color: '#8b92ff', marginLeft: '0.5rem' }}>(You)</span>}
                    </td>
                    <td style={{ fontWeight: isCurrentUser ? 'bold' : 'normal', color: isCurrentUser ? '#66bb6a' : 'inherit' }}>{user.total_xp}</td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      )}
    </>
  );
};

export default HomePage;