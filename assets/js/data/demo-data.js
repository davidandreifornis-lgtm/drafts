/**
 * Data: demo-data
 * Split out of the former app-logic.js — behavior unchanged.
 */

export const DEMO_INITIAL_INVENTORY = [
  {
    id: "TNR-001",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "ACCT",
    serialNumbers: [],
    quantity: 8,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Acctg Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-002",
    inkCode: "CF276A",
    brand: "HP",
    printerModel: "HP LaserJet Pro MFP M428fdn",
    color: "Black",
    department: "BD",
    serialNumbers: [],
    quantity: 6,
    reorderLevel: 3,
    supplier: "JAN A",
    location: "BD Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-003",
    inkCode: "Q2612A",
    brand: "Canon",
    printerModel: "Canon LBP 2900",
    color: "Black",
    department: "BMS",
    serialNumbers: [],
    quantity: 10,
    reorderLevel: 3,
    supplier: "JMD",
    location: "General Warehouse",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-004",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "LOGISTICS",
    serialNumbers: [],
    quantity: 5,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Logistics Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-005",
    inkCode: "CAN 045 HBK",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Black",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 7,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-006",
    inkCode: "CAN 045 HC",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Cyan",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 4,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-007",
    inkCode: "CAN 045 HY",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Yellow",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 4,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-008",
    inkCode: "CAN 045 HM",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Magenta",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 4,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-009",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 6,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Packaging Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-010",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "PURCHASING",
    serialNumbers: [],
    quantity: 5,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Purchasing Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-011",
    inkCode: "CF280A",
    brand: "HP",
    printerModel: "HP Color LaserJet Pro 400 MFP M425dn",
    color: "Black",
    department: "QC",
    serialNumbers: [],
    quantity: 3,
    reorderLevel: 3,
    supplier: "JMD",
    location: "QC Laboratory",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-012",
    inkCode: "Q2612A",
    brand: "Canon",
    printerModel: "Canon LBP 2900",
    color: "Black",
    department: "LOGISTICS",
    serialNumbers: [],
    quantity: 9,
    reorderLevel: 3,
    supplier: "JMD",
    location: "PM warehouse",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  }
];

export const DEMO_APPROVED_TICKETS = [
  {
    id: "TICKET-DEL-01",
    referenceNumber: "DEL-2026-00125",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "ABC Office Supplies",
    items: [
      {
        serialNumber: "SN-H682-001",
        inkCode: "HP-682-BLK",
        brand: "HP",
        printerModel: "HP DeskJet 2775",
        color: "Black",
        quantity: 20
      },
      {
        serialNumber: "SN-H682-002",
        inkCode: "HP-682-CYN",
        brand: "HP",
        printerModel: "HP DeskJet 2775",
        color: "Cyan",
        quantity: 15
      }
    ],
    createdAt: "2026-09-09T08:30:00"
  },
  {
    id: "TICKET-DEL-02",
    referenceNumber: "DEL-2026-00126",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "TechInk Distributors",
    items: [
      {
        serialNumber: "SN-CPG47-101",
        inkCode: "Canon PG-47",
        brand: "Canon",
        printerModel: "Canon PIXMA E410 / E470",
        color: "Black",
        quantity: 25
      },
      {
        serialNumber: "SN-CCL57-201",
        inkCode: "Canon CL-57",
        brand: "Canon",
        printerModel: "Canon PIXMA E410 / E470",
        color: "Tri-color",
        quantity: 20
      }
    ],
    createdAt: "2026-09-09T09:15:00"
  },
  {
    id: "TICKET-DEL-03",
    referenceNumber: "DEL-2026-00127",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "Universal Supplies Co.",
    items: [
      {
        serialNumber: "SN-EP003-BK1",
        inkCode: "Epson 003 Black",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Black",
        quantity: 30
      },
      {
        serialNumber: "SN-EP003-CY1",
        inkCode: "Epson 003 Cyan",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Cyan",
        quantity: 15
      }
    ],
    createdAt: "2026-09-09T10:00:00"
  },
  {
    id: "TICKET-DEL-04",
    referenceNumber: "DEL-2026-00128",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "Prime Office Gear",
    items: [
      {
        serialNumber: "SN-BR60-01",
        inkCode: "Brother BTD60BK",
        brand: "Brother",
        printerModel: "Brother DCP-T510W",
        color: "Black",
        quantity: 18
      },
      {
        serialNumber: "SN-BR50-01",
        inkCode: "Brother BT5000C",
        brand: "Brother",
        printerModel: "Brother DCP-T510W",
        color: "Cyan",
        quantity: 12
      }
    ],
    createdAt: "2026-09-09T10:30:00"
  },
  {
    id: "TICKET-REL-01",
    referenceNumber: "REL-2026-00451",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Neressa J. Siman",
    department: "LOGISTICS",
    purpose: "Toner request for Logistics Office printer",
    items: [
      {
        serialNumber: "",
        inkCode: "CRG-737",
        brand: "Canon",
        printerModel: "Canon MF237W",
        color: "Black",
        quantity: 1
      }
    ],
    createdAt: "2026-09-09T11:00:00"
  },
  {
    id: "TICKET-REL-02",
    referenceNumber: "REL-2026-00452",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Maria Santos",
    department: "ACCT",
    purpose: "Accounting office toner replenishment",
    items: [
      {
        serialNumber: "",
        inkCode: "CRG-737",
        brand: "Canon",
        printerModel: "Canon MF237W",
        color: "Black",
        quantity: 1
      }
    ],
    createdAt: "2026-09-09T11:30:00"
  },
  {
    id: "TICKET-REL-03",
    referenceNumber: "REL-2026-00453",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Alex Rivera",
    department: "PRODUCTION",
    purpose: "Production Office toner request",
    items: [
      {
        serialNumber: "SN-BR60-01",
        inkCode: "Brother BTD60BK",
        brand: "Brother",
        printerModel: "Brother DCP-T510W",
        color: "Black",
        quantity: 50 // Explicitly exceeds available stock (7) to test atomic rejection!
      }
    ],
    createdAt: "2026-09-09T12:00:00"
  },
  {
    id: "TICKET-REL-04",
    referenceNumber: "REL-2026-00454",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Sarah Gomez",
    department: "QC",
    purpose: "Product catalog print run",
    items: [
      {
        serialNumber: "SN-EP003-B01",
        inkCode: "Epson 003 Black",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Black",
        quantity: 3
      },
      {
        serialNumber: "SN-EP003-C01",
        inkCode: "Epson 003 Cyan",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Cyan",
        quantity: 2
      }
    ],
    createdAt: "2026-09-09T12:30:00"
  }
];

export const DEMO_INITIAL_TRANSACTIONS = [
  {
    id: "TXN-00001",
    type: "RECEIVED",
    referenceNumber: "DEL-2026-00100",
    inkId: "TNR-001",
    inkCode: "CRG-737",
    serialNumber: "N/A",
    brand: "Canon",
    color: "Black",
    quantity: 5,
    date: "2026-09-01",
    supplier: "INKRITE",
    givenTo: "",
    department: "",
    location: "",
    purpose: "Initial stock receipt",
    status: "APPROVED",
    createdAt: "2026-09-01T09:00:00"
  },
  {
    id: "TXN-00002",
    type: "RELEASED",
    referenceNumber: "REL-2026-00451",
    inkId: "TNR-004",
    inkCode: "CRG-737",
    serialNumber: "N/A",
    brand: "Canon",
    color: "Black",
    quantity: 1,
    date: "2026-09-09",
    supplier: "",
    givenTo: "Neressa J. Siman",
    department: "LOGISTICS",
    location: "Logistics Office",
    purpose: "Toner request for Logistics Office printer",
    status: "APPROVED",
    defective: false,
    createdAt: "2026-09-09T11:00:00"
  },
  {
    id: "TXN-00003",
    type: "RELEASED",
    referenceNumber: "REL-2026-00452",
    inkId: "TNR-001",
    inkCode: "CRG-737",
    serialNumber: "N/A",
    brand: "Canon",
    color: "Black",
    quantity: 1,
    date: "2026-09-09",
    supplier: "",
    givenTo: "Maria Santos",
    department: "ACCT",
    location: "Acctg Office",
    purpose: "Accounting office toner replenishment",
    status: "APPROVED",
    defective: false,
    createdAt: "2026-09-09T11:30:00"
  }
];

