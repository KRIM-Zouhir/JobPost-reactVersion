import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { createBrowserRouter, RouterProvider} from "react-router-dom"; 


import './index.css';
import HomePgae from './pages/main/home';
import SignUp from './pages/auth/signup';
import VerifyMail from './pages/auth/verify-mail';
import TokenValidation from './pages/auth/token';
import Login from './pages/auth/login';
import Profile from './pages/main/profile';
import Logout from './pages/auth/logout';
import Layout from './pages/components/layout';
import Jobs from './pages/main/jobs';
import Aboutus from './pages/main/aboutus';

import Applications from './pages/admin/applications';
import Posts from './pages/admin/posts';
import Users from './pages/admin/users';
import ForgotPwd from './pages/auth/forgotpwd';

const router = createBrowserRouter([
  {
    element: <Layout />,
    children: [
      { path: "/", element: <HomePgae />},
      { path: "/signup", element: <SignUp />},
      { path: "/login", element: <Login />},
      { path: "/profile", element: <Profile />},
      { path: "/forgot-password", element: <ForgotPwd />},
      { path: "/verifymail", element: <VerifyMail />},
      { path: "/verified", element: <TokenValidation />},
      { path: "/jobs", element: <Jobs />},
      { path: "/logout", element: <Logout />},
      { path: "/aboutus", element: <Aboutus />},
      { path: "/admin/applications", element: <Applications />},
      { path: "/admin/posts", element: <Posts />},
      { path: "/admin/users", element: <Users />}
    ],
  },

]);


createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <RouterProvider router={router} />
  </StrictMode>,
);
