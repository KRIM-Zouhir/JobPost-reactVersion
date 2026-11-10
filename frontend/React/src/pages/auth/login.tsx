import { useEffect, useState } from "react";
import { Link, useNavigate } from 'react-router-dom';

import { loginApi, verifySession } from '../api/auth.ts';
import { newVerLink } from '../api/verify.ts';


function Login(){
    const [message, setMessage] = useState("");
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");

    const redirect = useNavigate();

    const [notVerified, setNoVerified] = useState(false);

    useEffect(() =>{
        async function checkSession(){
            const session = await verifySession();
            if(session.success){
                redirect("/profile")
            }
        }

        checkSession();

    }, [redirect])

    async function handleLogin(e: React.FormEvent){
        e.preventDefault();
        const fetch = await loginApi(username, password);
        if(fetch.success){
            redirect("/profile");
        }else{
            setMessage(fetch.message);
            if(!fetch.verify){
                setNoVerified(true);
            }
        }
    };

    async function handleNewVerLink(username : string){
        const response = await newVerLink(username);
        setMessage(response.message);
    }




    return(
            <div className="bg-black text-white">
                <div className="min-h-screen flex flex-col items-center justify-center gap-5">   
                    <h3 className="text-center">Log In</h3>
                    
                    <div>
                        <p className="text-red-700">{message}</p>
                        <div 
                                className="flex w-full max-w-sm flex-col items-center justify-center gap-2 text-white">
                            <input value={username} onChange={(e) => setUsername(e.target.value)} placeholder="Username/Email" ></input>
                            <input value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Password" ></input>
                            <Link to="/signup">Create an account ?</Link>
                            <Link to="/forgot-password">Forgot your password ?</Link>
                            <br />
                            <button type="button" onClick={handleLogin}>Login</button>
                        </div>
                    </div>

                    {notVerified && username && (
                        <div className="">
                            <a onClick={() => handleNewVerLink(username)}>Send new verify link (email)</a>

                        </div>
                    )}
                    
                </div>
            </div>
    )
};

export default Login;
