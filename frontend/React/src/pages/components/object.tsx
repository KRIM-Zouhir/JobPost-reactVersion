export type Job = {
    postid: number;
    adminid: string;
    title: string;
    company: string;
    location: string;
    type: string;
    description: string;
    salary: string;
    place: string;
    status: string;
};

export type JobPublic = Omit<Job, "adminid">;
export type JobCreate = Omit<Job, "postid" | "adminid" | "status">;



export type User = {
    userid: string;
    username: string;
    email: string;
    role: string;
    is_verified: number;
    token: string;
    tokenexpire: string;
    date: string;
};

export type UserCreate = Omit<User, "userid" | "is_verified" | "token" | "tokenexpire" | "date"> & { password: string;};