export async function checkVerification(token : string){
    const response = await fetch("http://localhost:8000/verify.php", {
        method: "POST",
        headers: { "Content-Type" : "application/json" },
        credentials: "include",
        body: JSON.stringify({action: "checkVer", token})
    });

    return response.json();
}

export async function newVerLink(login : string){
    const response = await fetch("http://localhost:8000/verify.php", {
        method: "POST",
        headers: {"Content-Type" : "application/json"},
        credentials: "include",
        body: JSON.stringify({action: "newVerLink", login})
    });

    return response.json();
}