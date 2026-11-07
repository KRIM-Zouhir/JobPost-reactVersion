import { useEffect, useState } from "react";
import { Link, useNavigate } from 'react-router-dom';

import { loginApi, verifySession } from '../api/auth.ts'


function Login(){
    const [message, setMessage] = useState("");
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");

    const redirect = useNavigate();

    const [notVerified, setNoVerified] = useState(false);

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

    useEffect(() =>{
        async function checkSession(){
            const session = await verifySession();
            if(session.success){
                redirect("/profile")
            }
        }

        checkSession();

    }, [redirect])


    return(
            <div className="bg-black text-white">
                <div className="min-h-screen flex flex-col items-center justify-center gap-5">   
                    <h3 className="text-center">Log In</h3>
                    
                    <div>
                        <p className="text-red-700">{message}</p>
                        <form  onSubmit={handleLogin}
                                className="flex w-full max-w-sm flex-col items-center justify-center gap-2 text-white">
                            <input value={username} onChange={(e) => setUsername(e.target.value)} placeholder="Username/Email" ></input>
                            <input value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Password" ></input>
                            <Link to="/signup">Create an account ?</Link>
                            <br />
                            <button type="submit">Login</button>
                        </form>
                    </div>

                    {notVerified && (
                        <div className="">
                            <a href="">Send new verify link (email)</a>
                        </div>
                    )}
                    
                </div>
            </div>
    )
};

export default Login;
