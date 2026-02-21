const http = require("http");
const express = require("express");

const app = express();
app.use(express.json());

const PORT = 3000;
const GATEWAY_HOST = "localhost";
const GATEWAY_PORT = 18789;
const HOOKS_TOKEN = "kuma_2026-02-08_5a64b5c8e6c24e5bb35bf9d5";

const AGENT_MAP = {
  alma: "airbnb",
  clo: "staff",
  atlas: "main",
};

function postToGateway(path, body) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(body);
    const req = http.request(
      {
        hostname: GATEWAY_HOST,
        port: GATEWAY_PORT,
        path,
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Content-Length": Buffer.byteLength(data),
          Authorization: `Bearer ${HOOKS_TOKEN}`,
        },
      },
      (res) => {
        let body = "";
        res.on("data", (chunk) => (body += chunk));
        res.on("end", () => resolve({ status: res.statusCode, body }));
      }
    );
    req.on("error", reject);
    req.write(data);
    req.end();
  });
}

app.post("/agent-response", async (req, res) => {
  const { note_id, from_agent, content, guest_name, property_name } = req.body;

  if (!from_agent || !content) {
    return res.status(400).json({ error: "from_agent and content required" });
  }

  const agentId = AGENT_MAP[from_agent];
  if (!agentId) {
    return res.status(400).json({ error: `Unknown agent: ${from_agent}` });
  }

  const message = `[RESPUESTA] Para ${guest_name} (${property_name}): ${content}`;

  console.log(
    `[${new Date().toISOString()}] note=${note_id} agent=${agentId} msg=${message.slice(0, 80)}...`
  );

  try {
    const result = await postToGateway("/hooks/agent", {
      message,
      agentId,
      deliver: false,
      channel: "whatsapp",
    });

    console.log(`[${new Date().toISOString()}] gateway responded: ${result.status}`);
    res.json({ ok: true, gateway_status: result.status });
  } catch (err) {
    console.error(`[${new Date().toISOString()}] gateway error:`, err.message);
    res.status(502).json({ error: "Gateway unreachable", detail: err.message });
  }
});

app.get("/health", (_req, res) => res.json({ ok: true }));

app.listen(PORT, "0.0.0.0", () => {
  console.log(`Webhook proxy listening on 0.0.0.0:${PORT}`);
});
