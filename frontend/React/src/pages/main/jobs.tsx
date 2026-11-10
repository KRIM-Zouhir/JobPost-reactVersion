import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

import { getjobs } from '../api/jobs.ts'
import {verifySession} from '../api/auth.ts'
import { applyAPI, verifiedApp } from '../api/applications'
import type { Job } from "../components/object";



function Jobs(){
    const start = 0;
    const rows_per_page = 9;
    
    const [message, setMessage] = useState("");
    const [searchTerm, setSearchTerm] = useState("");
    const [sessionAuth, setSessionAuth] = useState(false);

    const [jobs, setJobs] = useState<Job[]>([]);
    const [selectedJob, setSelectedJob] = useState<Job | null>(null);
    const [appliedPostIds, setAppliedPostIds] = useState<Set<number>>(new Set());
    const [cvfile, setCv] = useState<File | null>(null);
    const [coverfile, setCover] = useState<File | null>(null);

    

    const redirect = useNavigate();


    useEffect(() => {

        async function checkSession(){
            const session = await verifySession();
            if(session.success){
                setSessionAuth(true);
            }
        }

        checkSession();
        handleLoadJobs();
        

    }, [redirect])

    useEffect(() => {
        async function handlePostIds() {
            const response = await verifiedApp();
            if(response.success){
                const ids = new Set<number>(response.posts_id.map((id: any) => Number(id)));
                setAppliedPostIds(ids);
            }
        }

        handlePostIds();
    }, [])

    useEffect(() => {
        handleLoadJobs();
    }, [searchTerm])

    async function handleLoadJobs() {
        const response = await getjobs(start, rows_per_page, searchTerm); 

        if(response.success){
            setJobs(response.data);
        }
    }


    function handleCvChange(e: React.ChangeEvent<HTMLInputElement>){
        if(e.target.files && e.target.files[0]){
            setCv(e.target.files[0]);
        }
    }
    function handleCoverChange(e: React.ChangeEvent<HTMLInputElement>){
        if(e.target.files && e.target.files[0]){
            setCover(e.target.files[0]);
        }
    }
    async function applyJob(e : React.FormEvent, postid: number, posttitle: string){
        e.preventDefault();
        if(!cvfile){
            setMessage("No CV file found !")
            return;
        } 

        const applyResult = await applyAPI(postid, posttitle, cvfile, coverfile)
        setMessage(applyResult.message)

    }

    


    return(
        <div className='bg-black text-white'>
            <div className='min-h-screen flex flex-col items-center justify-center'>

                <div className="text-white text-center">
                    <h1 className='text-center text-3xl font-bold pb-5'>Jobs</h1>
                        <input type="text" placeholder='Search jobs' className='p-2 rounded-md text-white' onChange={(e) => {setSearchTerm(e.target.value)}}/>
                </div>
                <p className="text-green-700">{message}</p>
                <div className='grid grid-cols-3 gap-4 p-4'>
                    {jobs.map((job) => 

                    <article key={job.postid} >
                        <h5>{job.title}</h5>
                        <h5>{job.company}</h5>
                        <p>{job.location} | {job.place}</p>
                        <p>{job.type}</p>
                        <p>{job.description}</p>
                        <p>{job.salary}</p>
                        <button type="button" onClick={() => setSelectedJob(job)}>
                            {appliedPostIds.has(job.postid) ? "Applied" : "Apply"}
                        </button>
                    </article>


                    )}

                </div>

                {selectedJob && sessionAuth ? 
                ( 
                    <div className='pt-5' id="applyForm">
                        <p>{selectedJob.title}</p>
                        <form onSubmit={(e) => applyJob(e, selectedJob.postid, selectedJob.title)}>
                            <input type="file" accept="file/*" onChange={handleCvChange}/>
                            <input type="file" accept="file/*" onChange={handleCoverChange}/>
                            <button type="submit">Submit</button>
                        </form>
                    </div>
                ) : selectedJob && !sessionAuth ?
                (
                    <div>
                        <p>Login to apply</p>
                    </div>
                ) : null
                }
            </div>
        </div>
    )
}

export default Jobs;