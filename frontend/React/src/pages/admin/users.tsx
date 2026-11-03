import React, { useEffect, useState, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';

import { getUsers, editUser } from '../api/auth';
import type { User } from "../components/object.tsx";
import { verifySession} from '../api/auth.ts'


function Users(){
    const start = 0;
    const row = 9;

    const redirect = useNavigate();

    const [ users, setUsers ] = useState<User[]>([]);
    const [ selectedUser, setSelectedUser ] = useState<User | null>(null)
    const [ editForm, setEditForm ] = useState(false);
    const [ deleteForm, setDeleteForm ] = useState(false);

    const verifyUsers = useCallback(async () => {
        const response = await getUsers(start, row);
        console.log(response);
        if(response.success){
            setUsers(response.data);
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
        verifyUsers();
    }, [verifyUsers])


    function handleEditUser(e: React.ChangeEvent<HTMLInputElement>){
        const { name, value } = e.target;
        setSelectedUser(prev => (prev ? { ...prev, [name]: value} : prev));
    }

    async function reqEditUser(data: User){
        const response = await editUser(data);
        if(response.success){
            verifyUsers();
        }
    }

    return(
        <div className='bg-black text-white'>
            <div className="min-h-screen flex flex-col justify-center items-center gap-4">
                <div className='text-center'></div>
                    <p>Users</p>

                    <div className='grid grid-cols-3 gap-4 p-4'>
                        {users.map((user) => 
                            <article key={user.userid}>
                                <h5>{user.username}</h5>
                                <h5>{user.email}</h5>
                                <h5>{user.role}</h5>
                                <h5>{user.token}</h5>
                                <h5>{user.tokenexpire}</h5>
                                <h5>{user.date}</h5>
                                <button type='button' onClick={() => {setEditForm(prev => !prev), setSelectedUser(user)}}>Edit</button>
                                <button type='button' onClick={() => {setDeleteForm(prev => !prev), setSelectedUser(user)}}>Delete</button>
                            </article>
                        )}

                    </div>
                    

                    {editForm && selectedUser && (
                        <div className=''>
                            <input name='username' type="text" value={selectedUser.username} onChange={handleEditUser} />
                            <input name='email' type="text" value={selectedUser.email} onChange={handleEditUser}/>
                            <input name='role' type="text" value={selectedUser.role} onChange={handleEditUser}/>
                            <input name='is_verified' type="text" value={selectedUser.is_verified} onChange={handleEditUser}/>
                            <input name='token' type="text" value={selectedUser.token} onChange={handleEditUser}/>
                            <input name='tokenexpire' type="text" value={selectedUser.tokenexpire} onChange={handleEditUser}/>
                            <input name='date' type="text" value={selectedUser.date} onChange={handleEditUser}/>
                            {selectedUser &&
                                <button type='button' onClick={() => {reqEditUser(selectedUser), setEditForm(prev => !prev)}}>Edit</button>
                            }
                        </div>
                    )}
            </div>
        </div>
    )
}

export default Users;
