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