import { useState } from "react";
import { forgotPwd } from "../api/auth";

function ForgotPwd(){

    const [ inputEmail, setInputEmail ] = useState("");
    const [ message, setMessage ] = useState("");

    async function reqForgotPwd(){
        const response = await forgotPwd(inputEmail);
        setMessage(response.message)
    }
    return(
        <div className="bg-black text-white">
            <div className="min-h-screen flex flex-col items-center justify-center gap-5">
                <p>{message}</p>
                <input value={inputEmail} type="text" onChange={(e) => {setInputEmail(e.target.value)}} placeholder="Type your account email"/>
                <button type="button" onClick={reqForgotPwd}>Submit</button>
            </div>

        </div>
    )
}

export default ForgotPwd;