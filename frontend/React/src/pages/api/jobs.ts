import type { JobPublic, JobCreate } from "../components/object";

export async function getjobs(start: number, rows: number, searchTerm: string){
    const response = await fetch("http://localhost:8000/post.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action:"get", start, rows, searchTerm})
    })

    return response.json();
}

export async function createJob(data : JobCreate){
    const response = await fetch("http://localhost:8000/post.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "create", data})
    });

    return response.json();
}

export async function editJob(data : JobPublic){
    const response = await fetch("http://localhost:8000/post.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "edit", data})
    });

    return response.json();
}

export async function deleteJob(data: JobPublic){
    const response = await fetch("http://localhost:8000/post.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "delete", data})
    });

    return response.json();
}