import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { FaUsers, FaBoxOpen, FaPlus, FaTrash, FaPencilAlt, FaBan, FaCheck, FaArrowLeft, FaTachometerAlt, FaDumbbell, FaClipboardList } from 'react-icons/fa';
import CreateExerciseForm from '../components/CreateExerciseForm';
import CreatePlanForm from '../components/CreatePlanForm';
import Modal from '../components/Modal';

const AdminDashboard = () => {
  const { session } = useAuth();
  const [users, setUsers] = useState([]);
  const [page, setPage] = useState(1);
  const [totalUsers, setTotalUsers] = useState(0);
  const limit = 10;
  const [plans, setPlans] = useState([]);
  const [exercises, setExercises] = useState([]);
  const [error, setError] = useState(null);
  const [isExerciseModalOpen, setIsExerciseModalOpen] = useState(false);
  const [isPlanModalOpen, setIsPlanModalOpen] = useState(false);
  const [exerciseSearchTerm, setExerciseSearchTerm] = useState('');
  const [debouncedExerciseSearchTerm, setDebouncedExerciseSearchTerm] = useState('');
  const [planSearchTerm, setPlanSearchTerm] = useState('');
  const [debouncedPlanSearchTerm, setDebouncedPlanSearchTerm] = useState('');
  const [activeTab, setActiveTab] = useState('exercises'); // 'exercises' or 'plans'

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedExerciseSearchTerm(exerciseSearchTerm);
    }, 300);

    return () => clearTimeout(handler);
  }, [exerciseSearchTerm]);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedPlanSearchTerm(planSearchTerm);
    }, 300);

    return () => clearTimeout(handler);
  }, [planSearchTerm]);

  const fetchPlans = useCallback(async () => {
    try {
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/plans`);
      if (!response.ok) throw new Error('Failed to fetch plans');
      const data = await response.json();
      setPlans(data);
    } catch (err) {
      // We can set a separate error state for this if needed
      console.error(err.message);
    }
  }, []);

  const fetchExercises = useCallback(async () => {
    if (!session) return;
    try {
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/exercises`, {
        headers: { 'Authorization': `Bearer ${session.access_token}` },
      });
      if (!response.ok) throw new Error('Failed to fetch exercises');
      const data = await response.json();
      setExercises(data);
    } catch (err) {
      console.error(err.message);
    }
  }, [session]);

  const fetchUsers = useCallback(async () => {
    try {
      if (!session) throw new Error('Authentication error');

      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/users?page=${page}&limit=${limit}`, {
        headers: {
          'Authorization': `Bearer ${session.access_token}`,
        },
      });
      if (!response.ok) throw new Error('Failed to fetch users');
      const data = await response.json();
      setUsers(data.users);
      setTotalUsers(data.totalCount);
    } catch (err) {
      setError(err.message);
    }
  }, [session, page]);

  useEffect(() => {
    fetchUsers();
    fetchPlans();
    fetchExercises();
  }, [fetchUsers, fetchPlans, fetchExercises]);
  
  const handleBanUser = async (userId) => {
    if (!window.confirm('Are you sure you want to ban this user?')) return;

    try {
      if (!session) throw new Error('Authentication error');

      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/users/${userId}/ban`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${session.access_token}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to ban user');
      }

      // Refresh the user list to show the updated status
      fetchUsers();

    } catch (err) {
      setError(err.message);
    }
  };

  const handleUnbanUser = async (userId) => {
    if (!window.confirm('Are you sure you want to unban this user?')) return;

    try {
      if (!session) throw new Error('Authentication error');

      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/users/${userId}/unban`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${session.access_token}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to unban user');
      }

      fetchUsers();
    } catch (err) {
      setError(err.message);
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!window.confirm('PERMANENTLY DELETE this user and all their data? This cannot be undone.')) return;
    try {
      if (!session) throw new Error('Authentication error');
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/users/${userId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${session.access_token}` },
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to delete user');
      }
      fetchUsers(); // Refresh list
    } catch (err) {
      setError(err.message);
    }
  };

  const handleDeletePlan = async (planId) => {
    if (!window.confirm('PERMANENTLY DELETE this plan and all its scheduled workouts?')) return;
    try {
      if (!session) throw new Error('Authentication error');
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/plans/${planId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${session.access_token}` },
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to delete plan');
      }
      fetchPlans(); // Refresh list
    } catch (err) {
      setError(err.message);
    }
  };

  const handleDeleteExercise = async (exerciseId) => {
    if (!window.confirm('PERMANENTLY DELETE this exercise from the library? This may fail if it is currently in use.')) return;
    try {
      if (!session) throw new Error('Authentication error');
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/exercises/${exerciseId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${session.access_token}` },
      });
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to delete exercise');
      }
      fetchExercises(); // Refresh list
    } catch (err) {
      // Display API-provided error message (e.g., "Cannot delete because it is in use")
      alert(err.message);
      setError(err.message);
    }
  };

  return (
    <div>
      <div className="page-header">
        <h1>Admin Dashboard</h1>
        <nav>
          <Link to="/" className="icon-link" style={{ color: '#8b92ff' }}><FaArrowLeft /> <span>Back home</span></Link>
        </nav>
      </div>
      <div className="admin-section">
        <h2><FaUsers /> User Management</h2>
        {error && <p style={{ color: '#ff6b6b' }}>Error: {error}</p>}
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map(user => (
              <tr key={user.id}>
                <td>{user.name}</td>
                <td>{user.is_banned ? 'Banned' : 'Active'}</td>
                <td className="action-cell">
                  <div className="action-buttons">
                    {user.is_banned ? (
                      <button onClick={() => handleUnbanUser(user.id)} className="icon-button" title="Unban" style={{ padding: '0.5rem 0.8rem' }}><FaCheck /></button>
                    ) : (
                      <button onClick={() => handleBanUser(user.id)} className="icon-button warning-button" title="Ban" style={{ padding: '0.5rem 0.8rem' }}><FaBan /></button>
                    )}
                    <button onClick={() => handleDeleteUser(user.id)} className="delete-button icon-button" title="Delete" style={{ padding: '0.5rem 0.8rem' }}><FaTrash /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '1rem', alignItems: 'center' }}>
          <button 
            disabled={page === 1} 
            onClick={() => setPage(page - 1)}
            style={{ padding: '0.5rem 1rem' }}
          >
            Previous
          </button>
          <span>Page {page} of {Math.ceil(totalUsers / limit) || 1} ({totalUsers} total users)</span>
          <button 
            disabled={page >= Math.ceil(totalUsers / limit)} 
            onClick={() => setPage(page + 1)}
            style={{ padding: '0.5rem 1rem' }}
          >
            Next
          </button>
        </div>
      </div>
      <div className="admin-section">
        <h2><FaBoxOpen /> Content Management</h2>
        
        <div className="tabs-container">
          <button 
            className={`tab-button ${activeTab === 'exercises' ? 'active' : ''}`}
            onClick={() => setActiveTab('exercises')}
          >
            Exercises Library
          </button>
          <button 
            className={`tab-button ${activeTab === 'plans' ? 'active' : ''}`}
            onClick={() => setActiveTab('plans')}
          >
            Training Plans
          </button>
        </div>

        <div className="admin-forms-container">
          {activeTab === 'exercises' && (
            <div>
              <div className="content-manage-header">
                <h3>Exercises</h3>
                <button onClick={() => setIsExerciseModalOpen(true)} className="icon-button"><FaPlus /> New Exercise</button>
              </div>
              <form className="search-bar" onSubmit={(e) => e.preventDefault()}>
                  <input
                      type="text"
                      placeholder="Search exercises by name..."
                      value={exerciseSearchTerm}
                      onChange={(e) => setExerciseSearchTerm(e.target.value)}
                      style={{ marginBottom: '1rem', padding: '0.5rem', width: 'calc(100% - 1rem)' }}
                  />
              </form>
              <div className="manage-plans-list">
                {exercises.filter(ex => ex.name.toLowerCase().includes(debouncedExerciseSearchTerm.toLowerCase())).map(ex => (
                  <div key={ex.id} className="manage-plan-item">
                    <span>{ex.name}</span>
                    <div className="manage-item-actions">
                      <Link to={`/admin/exercises/${ex.id}`} className="manage-action-btn manage-edit-btn" title="Manage"><FaPencilAlt /> <span style={{ marginLeft: '0.4rem' }}>Manage</span></Link>
                      <button onClick={() => handleDeleteExercise(ex.id)} className="manage-action-btn manage-delete-btn" title="Delete"><FaTrash /></button>
                    </div>
                  </div>
                ))}
                {exercises.filter(ex => ex.name.toLowerCase().includes(debouncedExerciseSearchTerm.toLowerCase())).length === 0 && (
                  <p style={{ color: '#aaa', padding: '1rem' }}>No exercises found.</p>
                )}
              </div>
            </div>
          )}

          {activeTab === 'plans' && (
            <div>
              <div className="content-manage-header">
                <h3>Training Plans</h3>
                <button onClick={() => setIsPlanModalOpen(true)} className="icon-button"><FaPlus /> New Plan</button>
              </div>
              <form className="search-bar" onSubmit={(e) => e.preventDefault()}>
                  <input
                      type="text"
                      placeholder="Search training plans by title..."
                      value={planSearchTerm}
                      onChange={(e) => setPlanSearchTerm(e.target.value)}
                      style={{ marginBottom: '1rem', padding: '0.5rem', width: 'calc(100% - 1rem)' }}
                  />
              </form>
              <div className="manage-plans-list">
                {plans.filter(plan => plan.title.toLowerCase().includes(debouncedPlanSearchTerm.toLowerCase())).map(plan => (
                  <div key={plan.id} className="manage-plan-item">
                    <span>{plan.title}</span>
                    <div className="manage-item-actions">
                      <Link to={`/admin/plans/${plan.id}`} className="manage-action-btn manage-edit-btn" title="Manage"><FaPencilAlt /> <span style={{ marginLeft: '0.4rem' }}>Manage</span></Link>
                      <button onClick={() => handleDeletePlan(plan.id)} className="manage-action-btn manage-delete-btn" title="Delete"><FaTrash /></button>
                    </div>
                  </div>
                ))}
                {plans.filter(plan => plan.title.toLowerCase().includes(debouncedPlanSearchTerm.toLowerCase())).length === 0 && (
                  <p style={{ color: '#aaa', padding: '1rem' }}>No training plans found.</p>
                )}
              </div>
            </div>
          )}
        </div>
      </div>

      <Modal isOpen={isExerciseModalOpen} onClose={() => setIsExerciseModalOpen(false)} title="Create New Exercise" icon={FaDumbbell}>
        <CreateExerciseForm onExerciseCreated={() => {
          fetchExercises();
          setIsExerciseModalOpen(false);
        }} />
      </Modal>

      <Modal isOpen={isPlanModalOpen} onClose={() => setIsPlanModalOpen(false)} title="Create New Training Plan" icon={FaClipboardList}>
        <CreatePlanForm onPlanCreated={() => {
          fetchPlans();
          setIsPlanModalOpen(false);
        }} />
      </Modal>
    </div>
  );
};

export default AdminDashboard;
