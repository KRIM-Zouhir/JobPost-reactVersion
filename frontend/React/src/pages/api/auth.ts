
import type { User } from "../components/object.tsx";

export async function signupApi(username: string, email: string, password: string,) {
    const response = await fetch("http://localhost:8000/auth.php" , {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "signup", username, email, password})
    });

    return response.json();
}

export async function loginApi(username: string, password: string,) {
    const response = await fetch("http://localhost:8000/auth.php" , {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "login", username, password})
    });

    return response.json();
}

export async function profileApi(){
    const response = await fetch("http://localhost:8000/auth.php" , {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "profile"})
    });

    return response.json();
}

export async function logoutApi(){
    const response = await fetch("http://localhost:8000/auth.php" , {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "logout"})
    });

    return response.json();
}

export async function verifySession(){
    const response = await fetch("http://localhost:8000/auth.php" , {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "verify-session"})
    });

    return response.json();
}

export async function getUsers(start: number, row: number){
    const response = await fetch("http://localhost:8000/auth.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "getUsers", start, row})
    });

    return response.json();
}

export async function editUser(infos: User){
    const response = await fetch("http://localhost:8000/auth.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "editUser", infos})
    });

    return response.json();
}