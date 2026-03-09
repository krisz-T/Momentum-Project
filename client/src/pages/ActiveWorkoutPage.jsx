import { useState, useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { FaPlay, FaPause, FaStepForward, FaStepBackward, FaStopCircle } from 'react-icons/fa';

const ActiveWorkoutPage = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { session } = useAuth();
  const { workout } = location.state || {}; // Get the workout data passed from the previous page

  const [timer, setTimer] = useState(() => {
    const savedState = localStorage.getItem('momentum_active_workout');
    if (savedState) {
      const parsed = JSON.parse(savedState);
      // Only restore timer if it's the same workout plan/type
      if (workout && parsed.workoutId === workout.id) {
        return parsed.timer;
      }
    }
    return 0;
  });

  const [isPaused, setIsPaused] = useState(() => {
    const savedState = localStorage.getItem('momentum_active_workout');
    if (savedState) {
      const parsed = JSON.parse(savedState);
      if (workout && parsed.workoutId === workout.id) {
        return parsed.isPaused;
      }
    }
    return false;
  });

  const [currentExerciseIndex, setCurrentExerciseIndex] = useState(() => {
    const savedState = localStorage.getItem('momentum_active_workout');
    if (savedState) {
      const parsed = JSON.parse(savedState);
      if (workout && parsed.workoutId === workout.id) {
        return parsed.currentExerciseIndex;
      }
    }
    return 0;
  });

  // Save state to localStorage whenever it changes
  useEffect(() => {
    if (workout) {
      const stateToSave = {
        workoutId: workout.id,
        timer,
        isPaused,
        currentExerciseIndex,
        lastUpdated: new Date().getTime()
      };
      localStorage.setItem('momentum_active_workout', JSON.stringify(stateToSave));
    }
  }, [timer, isPaused, currentExerciseIndex, workout]);

  useEffect(() => {
    if (!workout) {
      // If no workout data is passed, redirect back to plans page
      navigate('/plans');
      return;
    }

    let interval;
    if (!isPaused) {
      interval = setInterval(() => {
        setTimer(prev => prev + 1);
      }, 1000);
    }

    return () => clearInterval(interval); // Cleanup on component unmount or pause
  }, [isPaused, workout, navigate]);

  const formatTime = (seconds) => {
    const getSeconds = `0${seconds % 60}`.slice(-2);
    const minutes = `${Math.floor(seconds / 60)}`;
    const getMinutes = `0${minutes % 60}`.slice(-2);
    const getHours = `0${Math.floor(seconds / 3600)}`.slice(-2);
    return `${getHours}:${getMinutes}:${getSeconds}`;
  };

  const handleFinishWorkout = async () => {
    if (!session) {
      alert('Error: Not logged in.');
      return;
    }
    try {
      const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/workouts`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${session.access_token}`,
        },
        body: JSON.stringify({
          type: workout.workout_type,
          duration: timer, // Send the exact duration in seconds
        }),
      });
      if (!response.ok) {
        const errData = await response.json();
        throw new Error(errData.error || 'Failed to log workout.');
      }
      alert(`Workout Logged! You earned XP for ${formatTime(timer)} of work!`);
      localStorage.removeItem('momentum_active_workout'); // Clear saved state on finish
      navigate('/'); // Go back to home
    } catch (error) {
      alert(`Error: ${error.message}`);
    }
  };

  if (!workout) return null;

  const currentExercise = workout.workout_exercises[currentExerciseIndex];

  return (
    <div className="active-workout-container">
      <div className="timer-display">{formatTime(timer)}</div>

      <div className="current-exercise-card">
        <h2>Current Exercise</h2>
        <h3>{currentExercise?.exercises.name || 'Finished!'}</h3>
        {currentExercise && (
          <p className="sets-reps">
            {currentExercise.sets} sets of {currentExercise.reps || `${currentExercise.duration_seconds} seconds`}
          </p>
        )}
        {currentExercise?.exercises.video_url && (
          <div className="video-container">
            <iframe
              src={currentExercise.exercises.video_url}
              title="YouTube video player"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen></iframe>
          </div>
        )}
      </div>

      <div className="workout-controls" style={{ marginTop: '2rem' }}>
        <button
          onClick={() => setCurrentExerciseIndex(prev => prev - 1)}
          disabled={currentExerciseIndex <= 0}
          className="icon-button large-icon-button"
          style={{ backgroundColor: 'rgba(255,255,255,0.05)', color: '#fff', border: '1px solid rgba(255,255,255,0.2)', width: '60px', height: '60px', borderRadius: '50%', display: 'flex', justifyContent: 'center', alignItems: 'center', opacity: currentExerciseIndex <= 0 ? 0.3 : 1 }}
        >
          <FaStepBackward />
        </button>
        <button onClick={() => setIsPaused(!isPaused)} className="icon-button large-icon-button" style={{ backgroundColor: 'rgba(255,255,255,0.05)', color: '#fff', border: '1px solid rgba(255,255,255,0.2)', width: '60px', height: '60px', borderRadius: '50%', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
          {isPaused ? <FaPlay /> : <FaPause />}
        </button>
        <button
          onClick={() => setCurrentExerciseIndex(prev => prev + 1)}
          disabled={currentExerciseIndex >= workout.workout_exercises.length - 1}
          className="icon-button large-icon-button"
          style={{ backgroundColor: 'rgba(255,255,255,0.05)', color: '#fff', border: '1px solid rgba(255,255,255,0.2)', width: '60px', height: '60px', borderRadius: '50%', display: 'flex', justifyContent: 'center', alignItems: 'center', opacity: currentExerciseIndex >= workout.workout_exercises.length - 1 ? 0.3 : 1 }}
        >
          <FaStepForward />
        </button>
      </div>

      <div style={{ marginTop: '2rem' }}>
        <button onClick={handleFinishWorkout} className="finish-button icon-button" style={{ padding: '1rem 2rem', fontSize: '1.2rem', backgroundColor: '#ff6b6b', color: '#fff', border: 'none', borderRadius: '12px', fontWeight: 'bold' }}>
          <FaStopCircle />
          <span>Finish Workout</span>
        </button>
      </div>
    </div>
  );
};

export default ActiveWorkoutPage;