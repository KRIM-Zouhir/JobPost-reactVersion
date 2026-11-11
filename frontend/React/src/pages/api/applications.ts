
export async function applyAPI(postid : number, cv: File , cover: File | null){
    const formData = new FormData(); 
        formData.append("action", "apply");
        formData.append("postid", String(postid));
        formData.append("cvfile", cv);
        if(cover){
            formData.append("coverfile", cover);
        }

    const response = await fetch("http://localhost:8000/apply.php" , {
        method: "POST",
        credentials: "include",
        body: formData
    });

    return response.json();
}


export async function verifiedApp(){
    const formData = new FormData(); 
    formData.append("action", "verifyApp");

    const response = await fetch("http://localhost:8000/apply.php", {
        method: "POST",
        credentials: "include",
        body: formData
    });

    return response.json();
}

export async function getApps(start: number, rows: number, searchTerm: string){
    const formData = new FormData(); 
        formData.append("action", "getApp");
        formData.append("start", String(start));
        formData.append("rows", String(rows));
        formData.append("searchTerm", String(searchTerm));

    const response = await fetch("http://localhost:8000/apply.php", {
        method: "POST",
        credentials: "include",
        body: formData
    })

    return response.json();
}

export async function statusAdmin(appid: number, status: string){
    const formData = new FormData();
        formData.append("action", "statusApp");
        formData.append("appid", String(appid));
        formData.append("status", status);

    
    const response = await fetch("http://localhost:8000/apply.php", {
        method: "POST",
        credentials: "include",
        body: formData
    });

    return response.json();
}
