import { useState } from "react";
import { forgotPwd, verifyCode, resetPassword } from "../api/auth";
import { Link, useNavigate } from "react-router-dom";

function ForgotPwd(){

    const redirect = useNavigate();

    const [ inputEmail, setInputEmail ] = useState("");
    const [ inputCode, setInputCode ] = useState("");
    const [ inputNewPwd, setInputNewPassword ] = useState("");
    const [ inputConfirmPwd, setInputConfirmPassword ] = useState("");

    const [ message, setMessage ] = useState("");
    const [form, setForm] = useState(true);
    const [statusCode, setStatusCode] = useState(false);
    const [statusVerify, setStatusVerify] = useState(false);

    async function reqForgotPwd(){
        const response = await forgotPwd(inputEmail);
        setMessage(response.message);
        if(response.statuscode){
            setForm(false);
            setStatusCode(response.statuscode);
        }
    }

    async function reqVerifyCode(){
        const response = await verifyCode(inputEmail, inputCode);
        setMessage(response.message);
        if(response.statusverify){
            setStatusCode(false);
            setStatusVerify(response.statusverify);
        }
    }

    async function reqResetPassword(){
        if(inputNewPwd !== inputConfirmPwd){
            setMessage("Passwords do not match !");
            return;
        }
        const response = await resetPassword(inputEmail, inputNewPwd);
        setMessage(response.message);
        if(response.success){
            redirect("/login");
        }
    }

    

    return(
        <div className="bg-black text-white">
            <div className="min-h-screen flex flex-col items-center justify-center gap-5">
                {form && (
                    <div className="text-center flex flex-col items-center justify-center gap-2">
                        <h1 className="text-3xl font-bold">Forgot Password</h1>
                        <p>{message}</p>
                        <input value={inputEmail} type="text" onChange={(e) => {setInputEmail(e.target.value)}} placeholder="Type your account email"/>
                        <button type="button" onClick={reqForgotPwd}>Submit</button>
                    </div>
                )}
                {statusCode && (
                    <div className="text-center flex flex-col items-center justify-center gap-2">
                            <h2 className="text-2xl font-bold">Check your email</h2>
                            <p>We have sent a password reset code to your email.</p>

                            <input type="text" placeholder="Enter the code you received" className="" onChange={(e) => {setInputCode(e.target.value)}}/>
                            <button type="button" className="" onClick={reqVerifyCode}>Submit</button>
                        </div>
                )}
                {statusVerify && (
                    <div className="text-center flex flex-col items-center justify-center gap-2">
                            <h2 className="text-2xl font-bold">Code verified</h2>
                            <p>Your code has been successfully verified.</p>

                            <input type="text" placeholder="Enter your new password" className="" onChange={(e) => {setInputNewPassword(e.target.value)}}/>
                            <input type="text" placeholder="Confirm your new password" className="" onChange={(e) => {setInputConfirmPassword(e.target.value)}}/>
                            <button type="button" className="" onClick={reqResetPassword}>
                                Submit
                            </button>
                        </div>
                )}
            </div>
        </div>
    )
}

export default ForgotPwd;