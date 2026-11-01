import { useState, useEffect, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';

import {getApps, statusAdmin} from '../api/applications';
import { verifySession} from '../api/auth.ts'


type Application = {
    appid: number;
    userid: string;
    postid: number;
    posttitle: string;
    cvfilename: string;
    coverfilename: string;
    status: number;
    appdate: string;
}

function Applications(){
    const start = 0;
    const rows = 9;
    const redirect = useNavigate();

    const [message, setMessage] = useState("");
    const [applications, setApplications] = useState<Application[]>([]);

    const checkApps = useCallback(async () => {
        const session = await getApps(start, rows);
        if(session.success){
            setApplications(session.data);
        }
    }, [])

    useEffect(() => {
        async function checkSession(){
            const session = await verifySession();
            if(!session.success || session.user.role != "admin"){
                redirect("/login")
            }
        }

        checkSession();
    }, [redirect])

    useEffect(() => {

        checkApps();
    }, [checkApps])

    async function statusApp(appid: number, status: string){
        const session = await statusAdmin(appid, status);
        setMessage(session.message);
        if(session.success) {checkApps();}
    }


    return(
        <div className='bg-black text-white'>
            <div className="min-h-screen flex flex-col justify-center items-center gap-4">
                <div className='text-center'> <p>Applications</p></div>
                    <p className="text-green-700">{message}</p>


                    <div className='grid grid-cols-3 gap-4 p-4'>
                        {applications.map((application) => (

                            <article key={application.appid} >
                                <h5>{application.userid}</h5>
                                <h5>{application.postid}</h5>
                                <p>{application.posttitle} </p>


                                <button type="button"><a href={`http://localhost:8000/uploads/post_${application.postid}/${application.cvfilename}`}>CV</a></button>
                                
                                {application.coverfilename ? (
                                    <button type="button"><a href={`http://localhost:8000/uploads/post_${application.postid}/${application.coverfilename}`}>Cover</a></button>
                                ) : ( 
                                    <button type="button" disabled><a href={`http://localhost:8000/uploads/post_${application.postid}/${application.coverfilename}`} >No Cover</a></button>
                                )
                                
                                }
                                <p>{application.appdate}</p>
                                <p>{application.status}</p>
                                <button type="button" onClick={() => statusApp(application.appid, "accepted")}>
                                    Accept
                                </button>
                                <button type="button" onClick={() => statusApp(application.appid, "pending")}>
                                    Pending
                                </button>
                                <button type="button" onClick={() => statusApp(application.appid, "rejected")}>
                                    Deny
                                </button>
                            </article>


                        ))}

                    </div>
            </div>
        </div>
    )
}

export default Applications;
