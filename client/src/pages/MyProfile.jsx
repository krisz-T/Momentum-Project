import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { useAuth } from '../contexts/AuthContext';
import { FaArrowLeft, FaPencilAlt, FaSave, FaTimes, FaListAlt, FaHistory, FaTrophy, FaPlay, FaAward, FaUser, FaChartLine } from 'react-icons/fa';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';

const MyProfile = () => {
  const { userProfile, session, fetchProfile } = useAuth();
  const [badges, setBadges] = useState([]);
  const [enrollments, setEnrollments] = useState([]);
  const [workouts, setWorkouts] = useState([]);
  const [analyticsData, setAnalyticsData] = useState([]);
  const [loading, setLoading] = useState(true);

  const badgeDescriptions = {
    'First Workout': 'Awarded for logging your very first workout.',
    '5-Workout Mark': 'Awarded for completing 5 workouts.',
    '10 Workouts': 'Awarded for completing 10 workouts.',
    '50 Workouts': 'Awarded for completing 50 workouts.',
    '100 Workouts Club': 'Awarded for completing 100 workouts.',
    '500 Workouts!': 'Awarded for completing 500 workouts!',
    '1000 Workout Legend': 'Awarded for completing 1000 workouts.',
    'Plan Starter': 'Awarded for enrolling in your first training plan.',
  };

  // State for editing username
  const [isEditing, setIsEditing] = useState(false);
  const [newName, setNewName] = useState('');
  const [updateError, setUpdateError] = useState(null);


  useEffect(() => {
    const fetchProfileData = async () => {
      if (!session) return;
      setLoading(true);

      const headers = { 'Authorization': `Bearer ${session.access_token}` };
      const baseUrl = import.meta.env.VITE_API_BASE_URL;

      try {
        const [badgesRes, enrollmentsRes, workoutsRes, analyticsRes] = await Promise.all([
          fetch(`${baseUrl}/api/profile/badges`, { headers }),
          fetch(`${baseUrl}/api/profile/enrollments`, { headers }),
          fetch(`${baseUrl}/api/profile/workouts`, { headers }),
          fetch(`${baseUrl}/api/profile/analytics`, { headers }),
        ]);

        if (badgesRes.ok) setBadges(await badgesRes.json());
        if (enrollmentsRes.ok) setEnrollments(await enrollmentsRes.json());
        if (workoutsRes.ok) setWorkouts(await workoutsRes.json());
        if (analyticsRes.ok) setAnalyticsData(await analyticsRes.json());
        
      } catch (error) {
        console.error("Failed to fetch profile data:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchProfileData();
  }, [session]);

  useEffect(() => {
    if (userProfile) {
      setNewName(userProfile.name);
    }
  }, [userProfile]);

  const handleUpdateName = async (e) => {
    e.preventDefault();
    setUpdateError(null);
    try {
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/profile`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${session.access_token}`,
        },
        body: JSON.stringify({ name: newName }),
      });
      if (!response.ok) {
        const errData = await response.json();
        throw new Error(errData.error || 'Failed to update name');
      }
      // Refresh the profile in the global context
      await fetchProfile(session);
      setIsEditing(false);
    } catch (err) {
      setUpdateError(err.message);
    }
  };

  if (!userProfile) {
    return <div>Loading profile...</div>;
  }

  return (
    <div>
      <div className="page-header">
        <h1>My Profile</h1>
        <nav><Link to="/" className="icon-link" style={{ color: '#8b92ff' }}><FaArrowLeft /> <span>Back home</span></Link></nav>
      </div>

      <div className="profile-details">
        {isEditing ? (
          <form onSubmit={handleUpdateName} className="inline-form" style={{ padding: '0', backgroundColor: 'transparent', boxShadow: 'none', border: 'none', margin: '0 0 1rem 0' }}>
            <input type="text" value={newName} onChange={(e) => setNewName(e.target.value)} required style={{ flexGrow: 1 }} />
            <button type="submit" className="icon-button" style={{ backgroundColor: 'rgba(100,108,255,0.1)', color: '#8b92ff', borderColor: 'rgba(100,108,255,0.3)' }}><FaSave /></button>
            <button type="button" onClick={() => setIsEditing(false)} className="icon-button" style={{ backgroundColor: 'transparent', color: '#ccc', borderColor: 'transparent' }}><FaTimes /></button>
            {updateError && <p style={{ color: '#ff6b6b' }}>{updateError}</p>}
          </form>
        ) : (
          <div className="profile-header" style={{ marginBottom: '1rem' }}>
            <h2 style={{ margin: 0, fontSize: '2rem' }}>{userProfile.name}</h2>
            <button onClick={() => setIsEditing(true)} className="icon-button" title="Edit Name" style={{ background: 'transparent', border: 'none', color: '#8b92ff', padding: '0.4rem', boxShadow: 'none' }}><FaPencilAlt /></button>
          </div>
        )}
        <p style={{ color: '#aaa', margin: '0.5rem 0' }}>Role: <span style={{ color: '#fff', fontWeight: '500' }}>{userProfile.role}</span></p>
        <h3 style={{ marginTop: '1rem', color: '#a6abff' }}>Total XP: {userProfile.total_xp}</h3>
      </div>

      <div className="admin-section">
        <h3>My Active Plans</h3>
        {loading ? (
          <p>Loading plans...</p>
        ) : enrollments.length > 0 ? (
          <div className="plans-list">
            {enrollments.map(enrollment => (
              <div key={enrollment.id} className="plan-card">
                <h2>{enrollment.training_plans.title}</h2>
                <Link to={`/plans/${enrollment.plan_id}`} className="button-link icon-link"><FaPlay /> <span>Continue Plan</span></Link>
              </div>
            ))}
          </div>
        ) : <p>You are not enrolled in any active plans.</p>}
      </div>

      <div className="admin-section">
        <h3><FaChartLine /> Workout Analytics</h3>
        {loading ? (
          <p>Loading chart data...</p>
        ) : analyticsData.length > 0 ? (
          <div style={{ width: '100%', height: 300, marginTop: '1.5rem', backgroundColor: 'rgba(25, 26, 35, 0.6)', padding: '1rem', borderRadius: '12px', border: '1px solid rgba(255, 255, 255, 0.05)' }}>
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={analyticsData} margin={{ top: 5, right: 30, left: 0, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.1)" />
                <XAxis dataKey="date" stroke="#aaa" />
                <YAxis yAxisId="left" stroke="#8b92ff" />
                <YAxis yAxisId="right" orientation="right" stroke="#66bb6a" />
                <Tooltip contentStyle={{ backgroundColor: 'rgba(25, 26, 35, 0.9)', border: '1px solid rgba(255,255,255,0.1)', borderRadius: '8px' }} />
                <Legend />
                <Line yAxisId="left" type="monotone" dataKey="totalDuration" name="Duration (sec)" stroke="#8b92ff" strokeWidth={3} activeDot={{ r: 8 }} />
                <Line yAxisId="right" type="monotone" dataKey="xpGained" name="XP Gained" stroke="#66bb6a" strokeWidth={3} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        ) : (
          <p>Log your first workout to see your progress charts!</p>
        )}
      </div>

      <div className="admin-section">
        <h3>Recent Workouts</h3>
        {loading ? (
          <p>Loading history...</p>
        ) : workouts.length > 0 ? (
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Duration</th>
              </tr>
            </thead>
            <tbody>
              {workouts.map(w => (
                <tr key={w.id}>
                  <td>{new Date(w.date_logged).toLocaleDateString()}</td>
                  <td>{w.type}</td>
                  <td>{Math.floor(w.duration / 60)} min</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : <p>No workouts logged yet.</p>}
      </div>

      <div className="badges-section admin-section" style={{ borderTop: 'none', marginTop: '1rem' }}>
        <h3>My Badges</h3>
        {loading ? (
          <p>Loading badges...</p>
        ) : badges.length > 0 ? (
          <ul className="badge-list">
            {badges.map((badge) => (
              <li key={badge.badge_name} className="badge" title={badgeDescriptions[badge.badge_name] || 'An awesome achievement!'}>
                <FaAward />
                <span>{badge.badge_name}</span>
              </li>
            ))}
          </ul>
        ) : (
          <p>No badges earned yet. Keep going!</p>
        )}
      </div>
    </div>
  );
};

export default MyProfile;