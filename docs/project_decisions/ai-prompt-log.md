# AI Prompt Log for Momentum Project

**Version:** 1.0.0

This document logs significant interactions with the AI assistant (Gemini Code Assist Extension in VS Code and Antigravity IDE) during the development of the Momentum application. It includes the prompts given, the AI's suggestions, and my evaluation of those suggestions. The focus is on architectural decisions, mindful coding, and handling AI limitations.

---

## Format

Each entry follows a simple structure:

*   **Prompt:** The question or instruction I gave to the AI.
*   **AI Suggestion:** A summary of the code or architectural advice provided by the AI.
*   **My Evaluation:** My decision (Accepted, Modified, or Rejected) and the reasoning behind it.

---

## Log Entries

### Entry 1: Architectural Decision (Rejected) - Direct Client-Side DB Access

*   **Prompt:** "How do I fetch the user's workouts directly from Supabase in my React component to show on the profile page?"
*   **AI Suggestion:** The AI provided code using `@supabase/supabase-js` within a `useEffect` hook in the React frontend, suggesting using the Supabase `anon` key to query the `workouts` table directly from the browser.
*   **My Evaluation:** **Rejected.** While technically functional for simple apps, this approach directly violates the mandatory 3-Tier Architecture for this project. The presentation tier (client) must not access the data tier (database) directly. I prompted the AI again to create a new Express server endpoint (`GET /api/profile/workouts`), ensuring all database operations and business logic remain securely isolated in the backend server.

### Entry 2: Architectural Decision (Accepted) - State Management Strategy

*   **Prompt:** "I need to access the logged-in user's profile and authentication status across many different pages like the Header, Dashboard, and Profile settings. Should I pass it down as props from App.jsx?"
*   **AI Suggestion:** The AI warned against "prop drilling" (passing props through many intermediate components) and suggested using the React Context API as a centralized state management strategy. It generated the boilerplate for an `AuthContext.jsx` to wrap the application and a `useAuth` hook for easy access to the user state and login/logout functions.
*   **My Evaluation:** **Accepted.** This was a sound architectural decision that aligns with modern React best practices. It cleanly separates the global authentication state from individual component logic, making the codebase much more maintainable without the heavy overhead of an external library like Redux.

### Entry 3: Mindful Coding (Modified) - Incomplete Error Handling & Data Integrity

*   **Prompt:** "Write an Express endpoint to enroll a user in a training plan by inserting into user_plan_enrollments."
*   **AI Suggestion:** The AI generated a basic `POST /api/plans/:id/enroll` route that inserted a record into the `user_plan_enrollments` table. However, it only included a generic `try/catch` block returning a `500 Internal Server Error` regardless of what went wrong.
*   **My Evaluation:** **Modified.** The AI's code lacked defensive programming and mindful error handling. A user might try to enroll in a plan they are already actively enrolled in. I manually modified the code to first query the database for an existing active enrollment. If found, it now returns a `409 Conflict` (as defined in our Error Taxonomy), ensuring data integrity and providing a more helpful error to the frontend interface.

### Entry 4: Security Awareness (Rejected) - Unsafe UI Input Handling

*   **Prompt:** "Create an admin React component to add a new exercise with a YouTube video embed code."
*   **AI Suggestion:** The AI provided a component with a textarea for the embed code, and suggested rendering the user's raw input directly into the DOM using React's `dangerouslySetInnerHTML` to support generic embed tags from any source.
*   **My Evaluation:** **Rejected.** This suggestion introduced a severe Cross-Site Scripting (XSS) vulnerability. An attacker (even an admin whose account was compromised) could input a malicious `<script>` tag. I rejected this approach entirely because blind copy-pasting here would compromise the system. Instead, I designed the database to only store the `video_url` string, and I wrote the React code to render it strictly within a controlled `<iframe>` tag's `src` attribute, neutralizing the XSS threat.

### Entry 5: AI Limitation (Modified) - PostgreSQL Specific Syntax Errors

*   **Prompt:** "Write the SQL to create the `user_plan_enrollments` table with foreign keys linking to `users` and `training_plans`."
*   **AI Suggestion:** The AI generated the `CREATE TABLE` statement but hallucinated an incorrect syntax for the inline foreign key definitions, using `FOREIGN KEY plan_id REFERENCES plans(id)` directly after the column type without the proper `CONSTRAINT` declaration required by strict PostgreSQL standards.
*   **My Evaluation:** **Modified.** The AI's SQL produced syntax errors when executed in the Supabase SQL editor. AI models occasionally struggle with strict dialect-specific database syntax. I had to manually consult the PostgreSQL documentation to correct the foreign key definitions (e.g., `REFERENCES public.users(id) ON DELETE CASCADE`) to ensure the relational integrity was correctly established. This highlighted that AI is a helpful starting point, but manual verification is essential for database migrations.

### Entry 6: Performance & Scalability (Modified) - Dynamic Leaderboard Filtering

*   **Prompt:** "How do I make the leaderboard filterable by 'This Week' and 'This Month'?"
*   **AI Suggestion:** The AI suggested keeping the existing `GET /api/leaderboard` route as-is, and then having the React frontend fetch *all* workouts from the database, filter them by date in the user's browser, calculate the XP, sort the users, and slice the top 10.
*   **My Evaluation:** **Modified & Rejected.** While that logic works for 5 early users, sending thousands of workout rows to the client browser is a massive performance bottleneck and bandwidth waste. I rejected this client-side bloat. Instead, I prompted the AI to rewrite the `GET /api/leaderboard` endpoint to accept a `?timeframe=` query parameter. This forces the Express backend to securely query the `workouts` table, aggregate the XP mathematically in memory on the robust server, and *only* send the calculated top 10 array (`[ {id, name, total_xp} ]`) over the network to the client. This architectural decision ensures the app remains scalable as the user base grows.

---
