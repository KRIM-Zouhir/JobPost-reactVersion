import { useState, useEffect, useCallback } from "react";
import { useNavigate } from 'react-router-dom';

import { createJob, getjobs, editJob, deleteJob } from "../api/jobs";
import { verifySession} from '../api/auth.ts'
import type { Job, JobCreate, JobPublic } from "../components/object.tsx";





function Posts(){
    const redirect = useNavigate();
    const [message, setMessage] = useState("");
    
    const start = 0;
    const rows_per_page = 9;

    const [posts, setPosts] = useState<Job[]>([]);
    const [selectedPost, setSelectedPost] = useState<Job | null>(null);



    const [title, setTitle] = useState("");
    const [company, setCompany] = useState("");
    const [location, setLocation] = useState("");
    const [type, setType] = useState("");
    const [description, setDescription] = useState("");
    const [salary, setSalary] = useState("");
    const [place, setPlace] = useState("");
    
    const [showForm, setShowForm] = useState(false);
    const [editForm, setEditForm] = useState(false);
    const [deleteForm, setDeleteForm] = useState(false);
    
    const [confirm, setConfirm] = useState("");

    const checkPosts = useCallback(async () => {
        const response = await getjobs(start, rows_per_page); 
        if(response.success){
            setPosts(response.data);
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
        checkPosts();
    }, [checkPosts])
    
    
    function handleEditChange(e: React.ChangeEvent<HTMLInputElement>){
        const { name, value } = e.target;
        setSelectedPost(prev => (prev ? { ...prev, [name]: value } : prev));
    }
    
    async function createPost(data: JobCreate){
        const result = await createJob(data);
        if(result.success){
            setMessage(result.message);
            checkPosts();
        }
    }

    async function editPost(data: JobPublic){
        const result = await editJob(data);
        if(result.success){
            setMessage(result.message);
            checkPosts();
        }
    }

    async function removePost(data: JobPublic){
        const result = await deleteJob(data);
        if(result.success){
            setMessage(result.message);
            checkPosts();
        }
    }
    
    
    return(
        <div className='bg-black text-white'>
            <div className="min-h-screen flex flex-col justify-center items-center gap-4">
                <div className='text-center'></div>
                    <p>Posts</p>

                    <div className="mt-5 text-center">
                        <button type="button" onClick={() => (setShowForm(prev => !prev))}>
                            Add post
                        </button>

                        {showForm && (
                            <div className="">
                                <input type="text" value={title} placeholder="Title" onChange={(e) => setTitle(e.target.value)} />
                                <input type="text" value={company} placeholder="Company" onChange={(e) => setCompany(e.target.value)} />
                                <input type="text" value={location} placeholder="Location" onChange={(e) => setLocation(e.target.value)}/>
                                <input type="text" value={type} placeholder="Type" onChange={(e) => setType(e.target.value)}/>
                                <input type="text" value={description} placeholder="Description" onChange={(e) => setDescription(e.target.value)}/>
                                <input type="text" value={salary} placeholder="Salary" onChange={(e) => setSalary(e.target.value)}/>
                                <input type="text" value={place} placeholder="Place" onChange={(e) => setPlace(e.target.value)}/>
                                <button type="button" onClick={() => {createPost({title, company, location, type, description, salary, place}), setShowForm(prev => !prev)}}>Create</button>
                            </div>
                        )}
                    </div>

                    <div className='grid grid-cols-3 gap-4 p-4'>
                        {posts.map((post) => 
                            <article key={post.postid} >
                                <h5>{post.adminid}</h5>
                                <h5>{post.title}</h5>
                                <h5>{post.company}</h5>
                                <p>{post.location} | {post.place}</p>
                                <p>{post.type}</p>
                                <p>{post.description}</p>
                                <p>{post.salary}</p>
                                <p>{post.status}</p>
                                <button type="button" onClick={() => {setSelectedPost(post);
                                                                    setEditForm(prev => !prev) }}>
                                    Edit post
                                </button>

                                <button type="button" onClick={() => (setSelectedPost(post),
                                                                    setDeleteForm(prev => !prev))}>
                                    Delete post
                                </button>

                            </article>
                        )}

                    </div>

                    <div className="">
                        {editForm && selectedPost && (
                            <div className="">
                                <input name="title" type="text" value={selectedPost.title} onChange={handleEditChange} placeholder="Title"/>
                                <input name="company" type="text" value={selectedPost.company} onChange={handleEditChange} placeholder="Company"/>
                                <input name="location" type="text" value={selectedPost.location} onChange={handleEditChange} placeholder="Location"/>
                                <input name="type" type="text" value={selectedPost.type} onChange={handleEditChange} placeholder="Type"/>
                                <input name="description" type="text" value={selectedPost.description} onChange={handleEditChange} placeholder="Description"/>
                                <input name="salary" type="text" value={selectedPost.salary} onChange={handleEditChange} placeholder="Salary"/>
                                <input name="place" type="text" value={selectedPost.place} onChange={handleEditChange} placeholder="Place"/>
                                <input name="status" type="text" value={selectedPost.status} onChange={handleEditChange} placeholder="Status"/>
                                <button type="button" onClick={() => {editPost(selectedPost), setEditForm(prev => !prev)}}>Edit</button>
                            </div>)
                        }
                    </div>

                    <div>
                        {deleteForm && selectedPost && (
                            <div>
                                <h3>Delete {selectedPost.title} ?</h3>
                                <input type="text" placeholder="Confirm" onChange={(e) => setConfirm(e.target.value)}/>
                                {confirm.toLocaleLowerCase() === "confirm" &&
                                    <button type="button" onClick={() => {removePost(selectedPost), setDeleteForm(prev => !prev)}}>Delete</button>
                                }
                            </div>
                        )

                        }
                    </div>


            </div>
        </div>
    )
}

export default Posts;
