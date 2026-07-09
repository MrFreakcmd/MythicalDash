class Servers {
    public static async getServers() {
        const response = await fetch('/api/user/session/servers', {
            method: 'GET',
        });
        const data = await response.json();
        return data.servers;
    }

    public static async getQueuedServers() {
        const response = await fetch('/api/user/session/servers', {
            method: 'GET',
        });
        const data = await response.json();
        return data.servers_queue;
    }

    public static async getResources() {
        const response = await fetch('/api/user/session/resources', {
            method: 'GET',
        });
        const data = await response.json();
        return data.resources;
    }
}

export default Servers;
