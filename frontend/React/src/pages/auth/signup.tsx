import { useState, useEffect } from "react";
import { signupApi, verifySession } from '../api/auth.ts'
import { Link, useNavigate } from 'react-router-dom';

function SignUp (){
    const [message, setMessage] = useState("");
    const [username, setUsername] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [confirmpassword, setConfirmPassword] = useState("");

    const redirect = useNavigate();

    useEffect(() =>{
        async function checkSession(){
            const session = await verifySession();
            if(session.success){
                redirect("/profile")
            }
        }

        checkSession();
    }, [redirect])

    async function handleSignup(e: React.FormEvent){
        e.preventDefault();

        const fetch = await signupApi(username, email, password);
        console.log(fetch);
        setMessage(fetch.message);
        if(fetch.success){
            redirect('/verifymail')
        }
    };

    return(
            <div className="min-h-screen bg-black flex flex-col items-center justify-center">   
                <p className="text-white">{message}</p>
                    <div className="flex w-full max-w-sm flex-col items-center justify-center gap-2 text-white">
                        <input value={username} onChange={(e) => setUsername(e.target.value)} placeholder="Username" ></input>
                        <input value={email} onChange={(e) => setEmail(e.target.value)} placeholder="Email" ></input>
                        <input value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Password" ></input>
                        <input value={confirmpassword} onChange={(e) => setConfirmPassword(e.target.value)} placeholder="Confirm Password" ></input>
                        <Link to="/login">You already have one?</Link>

                        <button type="button" onClick={handleSignup}>Create</button>
                    </div>
            </div>
    )
};

export default SignUp;
