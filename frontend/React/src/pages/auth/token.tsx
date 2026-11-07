import { useState, useEffect } from "react";
import {useSearchParams } from "react-router-dom";
import { checkVerification } from "../api/verify";

function TokenValidation(){
    const [message, setMessage] = useState("Verifying ...");
    
    const [searchParam] = useSearchParams();
    const token = searchParam.get("token");
    
    async function handleVerToken(token : string){
        const fetch = await checkVerification(token);
        setMessage(fetch.message);
    
    }

    useEffect(() => {
        if(token){
            handleVerToken(token);
        }else{
            setMessage("Invalid Link !  ")
        }
    }, [token])


    
    return(
        <div className="min-h-screen bg-black text-white flex flex-col items-center justify-center">
            <p>{message}</p>
        </div>
    );
}

export default TokenValidation;